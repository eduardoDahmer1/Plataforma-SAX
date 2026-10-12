<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;

class BlogProductCarouselService
{
    public const MAX_PRODUCTS = 20;
    public const MAX_CAROUSELS = 10;

    // Blocks contain a heading and IDs; catalog data is resolved on each visit.
    private const BLOCK_PATTERN = '~<div\b(?=[^>]*\bdata-blog-products\s*=)[^>]*>.*?</div>~is';

    public function blocks(string $content): array
    {
        preg_match_all(self::BLOCK_PATTERN, $content, $matches);

        return array_map(function (string $html): array {
            $raw = $this->attribute($html, 'data-blog-products');
            $ids = preg_match('/^[1-9]\d*(?:,[1-9]\d*)*$/D', $raw)
                ? array_values(array_unique(array_map('intval', explode(',', $raw))))
                : [];

            return [
                'html' => $html,
                'ids' => $ids,
                'title' => $this->attribute($html, 'data-title'),
            ];
        }, $matches[0]);
    }

    private function attribute(string $html, string $name): string
    {
        $openingTag = substr($html, 0, strpos($html, '>') + 1);
        preg_match('~\b'.preg_quote($name, '~').'\s*=\s*([\'\"])(.*?)\1~is', $openingTag, $match);

        return html_entity_decode($match[2] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public function validate(string $content): void
    {
        $blocks = $this->blocks($content);
        if (count($blocks) > self::MAX_CAROUSELS) {
            throw ValidationException::withMessages(['content' => 'El artículo puede tener hasta 10 carruseles de productos.']);
        }
        foreach ($blocks as $block) {
            if (!$block['ids'] || count($block['ids']) > self::MAX_PRODUCTS || mb_strlen($block['title']) > 120) {
                throw ValidationException::withMessages(['content' => 'Cada carrusel debe tener entre 1 y 20 productos y un título de hasta 120 caracteres.']);
            }
        }
    }

    public function render(string $content): string
    {
        $blocks = $this->blocks($content);
        if (!$blocks) {
            return $content;
        }

        // Bound queries even if historical HTML bypassed the current validation.
        $ids = collect(array_slice($blocks, 0, self::MAX_CAROUSELS))
            ->flatMap(fn ($block) => array_slice($block['ids'], 0, self::MAX_PRODUCTS))
            ->unique()->values();
        $products = VisibleCatalogProductsService::builder()
            ->with(['brand', 'translations' => fn ($query) => $query->where('locale', translation_locale())])
            ->whereIn('products.id', $ids)->get()->keyBy('id');

        $index = 0;
        return preg_replace_callback(self::BLOCK_PATTERN, function () use ($blocks, $products, &$index): string {
            $block = $blocks[$index++];
            if ($index > self::MAX_CAROUSELS) {
                return '';
            }
            $items = collect(array_slice($block['ids'], 0, self::MAX_PRODUCTS))
                ->map(fn ($id) => $products->get($id))->filter()->values();
            if ($items->isEmpty()) {
                return '';
            }

            return view('blogs.partials.product-carousel', [
                'items' => $items,
                'title' => mb_substr($block['title'], 0, 120),
                'carouselId' => 'blog-products-'.$index,
            ])->render();
        }, $content) ?? $content;
    }
}
