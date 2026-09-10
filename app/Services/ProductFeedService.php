<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;
use XMLWriter;

class ProductFeedService
{
    public const FEED_PATH = 'feeds/products.xml';

    private const METADATA_PATH = 'feeds/products.meta.json';

    public function __construct(
        private readonly StoreControlService $storeControl,
        private readonly StoreTaxonomyService $taxonomy,
    ) {
    }

    public function generate(): array
    {
        $disk = Storage::disk('public');
        $directory = dirname($disk->path(self::FEED_PATH));

        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new RuntimeException('Não foi possível criar a pasta do XML de produtos.');
        }

        $lock = fopen($directory.'/products.lock', 'c');
        if ($lock === false || ! flock($lock, LOCK_EX | LOCK_NB)) {
            if (is_resource($lock)) {
                fclose($lock);
            }

            throw new RuntimeException('Já existe uma geração de XML em andamento. Aguarde alguns instantes.');
        }

        $temporaryFeed = $directory.'/products.'.bin2hex(random_bytes(8)).'.tmp';

        try {
            $count = $this->writeFeed($temporaryFeed);
            $destination = $disk->path(self::FEED_PATH);

            if (! rename($temporaryFeed, $destination)) {
                throw new RuntimeException('Não foi possível publicar o novo XML de produtos.');
            }

            $metadata = [
                'generated_at' => now()->toIso8601String(),
                'count' => $count,
                'profile' => $this->storeControl->storeProfile(),
            ];

            $this->writeMetadataAtomically($directory, $metadata);

            return $this->status();
        } catch (Throwable $exception) {
            if (is_file($temporaryFeed)) {
                @unlink($temporaryFeed);
            }

            throw $exception;
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    public function status(): array
    {
        $disk = Storage::disk('public');
        $exists = $disk->exists(self::FEED_PATH);
        $metadata = [];

        if ($disk->exists(self::METADATA_PATH)) {
            $metadata = json_decode($disk->get(self::METADATA_PATH), true) ?: [];
        }

        $generatedAt = $metadata['generated_at'] ?? null;
        if (! $generatedAt && $exists) {
            $generatedAt = date(DATE_ATOM, $disk->lastModified(self::FEED_PATH));
        }

        return [
            'exists' => $exists,
            'url' => url('/feeds/products.xml'),
            'path' => $disk->path(self::FEED_PATH),
            'generated_at' => $generatedAt,
            'count' => isset($metadata['count']) ? (int) $metadata['count'] : null,
            'profile' => $metadata['profile'] ?? null,
            'size' => $exists ? $disk->size(self::FEED_PATH) : null,
        ];
    }

    public function exists(): bool
    {
        return Storage::disk('public')->exists(self::FEED_PATH);
    }

    public function absolutePath(): string
    {
        return Storage::disk('public')->path(self::FEED_PATH);
    }

    private function writeFeed(string $path): int
    {
        $writer = new XMLWriter();
        if (! $writer->openUri($path)) {
            throw new RuntimeException('Não foi possível abrir o arquivo temporário do XML.');
        }

        $writer->setIndent(true);
        $writer->setIndentString('  ');
        $writer->startDocument('1.0', 'UTF-8');
        $writer->startElement('rss');
        $writer->writeAttribute('version', '2.0');
        $writer->writeAttribute('xmlns:g', 'http://base.google.com/ns/1.0');
        $writer->startElement('channel');
        $storeName = $this->storeControl->isOtica() ? 'VISTA&CO' : 'SAX';
        $writer->writeElement('title', $storeName.' - Produtos');
        $writer->writeElement('link', url('/'));
        $writer->writeElement('description', 'Catálogo de produtos atualizado pelo painel administrativo.');

        $count = 0;
        $query = VisibleCatalogProductsService::builder()
            ->with([
                'brand:id,name',
                'category:id,name,slug',
                'subcategory:id,name,slug',
                'categoriasFilhas:id,name,slug',
            ]);

        if ($this->storeControl->isOtica()) {
            $opticalCategoryIds = $this->taxonomy
                ->categories(Category::query())
                ->select('categories.id');

            $query->whereIn('products.category_id', $opticalCategoryIds);
        }

        $query->orderBy('products.id')->chunkById(250, function ($products) use ($writer, &$count): void {
            foreach ($products as $product) {
                $this->writeProduct($writer, $product);
                $count++;
            }
        }, 'products.id', 'id');

        $writer->endElement();
        $writer->endElement();
        $writer->endDocument();
        $writer->flush();

        return $count;
    }

    private function writeProduct(XMLWriter $writer, Product $product): void
    {
        $title = trim((string) ($product->name ?: $product->external_name ?: $product->sku));
        $description = $this->plainText($product->description ?: $title);
        $identifier = trim((string) $product->sku) ?: 'product-'.$product->id;
        $productUrl = route('produto.show', $product->slug ?: $product->id);
        $imageUrl = $this->absoluteUrl($product->photo_url);
        $productType = collect([
            $product->category?->name,
            $product->subcategory?->name,
            $product->categoriasFilhas?->name,
        ])->filter()->implode(' > ');
        $gtin = trim((string) $product->getAttribute('gtin'));
        $mpn = trim((string) ($product->getAttribute('mpn') ?: $product->ref_code));

        $writer->startElement('item');
        $this->googleElement($writer, 'id', $identifier);
        $this->googleElement($writer, 'title', $title);
        $this->googleElement($writer, 'description', $description);
        $this->googleElement($writer, 'link', $productUrl);
        $this->googleElement($writer, 'image_link', $imageUrl);
        $this->googleElement($writer, 'availability', 'in_stock');
        $this->googleElement($writer, 'price', number_format((float) $product->price, 2, '.', '').' USD');
        $this->googleElement($writer, 'condition', 'new');

        if ($product->brand?->name) {
            $this->googleElement($writer, 'brand', $product->brand->name);
        }
        if ($productType !== '') {
            $this->googleElement($writer, 'product_type', $productType);
        }
        if ($gtin !== '') {
            $this->googleElement($writer, 'gtin', $gtin);
        }
        if ($mpn !== '') {
            $this->googleElement($writer, 'mpn', $mpn);
        }
        if ($gtin === '' && $mpn === '') {
            $this->googleElement($writer, 'identifier_exists', 'no');
        }

        $writer->endElement();
    }

    private function googleElement(XMLWriter $writer, string $name, string $value): void
    {
        // O namespace já está declarado no elemento raiz; assim o XML fica
        // compacto sem repetir xmlns:g em cada campo do produto.
        $writer->writeElement('g:'.$name, $value);
    }

    private function absoluteUrl(?string $value): string
    {
        $value = trim((string) $value);
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        return url('/'.ltrim($value, '/'));
    }

    private function plainText(?string $value): string
    {
        $value = html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = preg_replace('/\s+/u', ' ', $value) ?: $value;

        return mb_substr(trim($value), 0, 5000);
    }

    private function writeMetadataAtomically(string $directory, array $metadata): void
    {
        $temporaryMetadata = $directory.'/products.meta.'.bin2hex(random_bytes(8)).'.tmp';
        $destination = Storage::disk('public')->path(self::METADATA_PATH);
        $contents = json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($contents === false || file_put_contents($temporaryMetadata, $contents, LOCK_EX) === false) {
            @unlink($temporaryMetadata);
            throw new RuntimeException('O XML foi criado, mas não foi possível salvar os dados da geração.');
        }

        if (! rename($temporaryMetadata, $destination)) {
            @unlink($temporaryMetadata);
            throw new RuntimeException('O XML foi criado, mas não foi possível publicar os dados da geração.');
        }
    }
}
