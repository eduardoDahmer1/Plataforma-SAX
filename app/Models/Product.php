<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Product extends Model
{
    public const MINIMUM_VISIBLE_PRICE = 3.0;

    protected $table = 'products';

    protected $fillable = [
        'sku',
        'product_type',
        'affiliate_link',
        'user_id',
        'category_id',
        'subcategory_id',
        'childcategory_id',
        'attributes',
        'slug',
        'photo',
        'thumbnail',
        'file',
        'size',
        'size_qty',
        'size_price',
        'color',
        'previous_price',
        'views',
        'status',
        'is_outlet',
        'status_before_outlet',
        'views',
        'colors',
        'product_condition',
        'is_meta',
        'youtube',
        'type',
        'license',
        'license_qty',
        'link',
        'platform',
        'region',
        'licence_type',
        'measure',
        'featured',
        'best',
        'top',
        'hot',
        'latest',
        'big',
        'trending',
        'sale',
        'is_discount',
        'discount_date',
        'whole_sell_qty',
        'whole_sell_discount',
        'is_catalog',
        'catalog_id',
        'ref_code',
        'ref_code_int',
        'mpn',
        'free_shipping',
        'max_quantity',
        'weight',
        'width',
        'height',
        'length',
        'gallery',
        'external_name',
        'ftp_hash',
        'color_qty',
        'color_price',
        'being_sold',
        'vendor_min_price',
        'vendor_max_price',
        'color_gallery',
        'material',
        'material_gallery',
        'material_qty',
        'material_price',
        'show_price',
        'show_in_navbar',
        'product_size',
        'product_role',
        'synced',
        'gtin',
        'promotion_price',
        'updated_by',
        'admin_edited_at',
        'name',
        'description',
        'price',
        'color',
        'size',
        'stock',
        'stores',
        'brand_id',
        'parent_id',
        'color_parent_id',
    ];

    // Casts para JSON/array
    protected $casts = [
        'gallery' => 'array',
        'price' => 'float',
        'stores' => 'array',
        'stock' => 'integer',
        'admin_edited_at' => 'datetime',
        'is_outlet' => 'boolean',
        'status_before_outlet' => 'boolean',
    ];

    protected static ?bool $hasColorParentColumn = null;
    protected static ?bool $hasColorColumn = null;
    protected static array $familyColorCache = [];

    protected static function booted(): void
    {
        static::addGlobalScope('minimum_visible_price', function (Builder $builder) {
            $builder->where(
                $builder->getModel()->qualifyColumn('price'),
                '>=',
                self::MINIMUM_VISIBLE_PRICE
            );
        });
    }

    // URL da foto do produto
    public function getPhotoUrlAttribute()
    {
        if ($this->photo && Storage::disk('public')->exists($this->photo)) {
            return Storage::url($this->photo);
        }
        return asset('storage/uploads/noimage.webp');
    }

    // Produto tem ao menos uma imagem válida (foto principal ou galeria) já existente no disco
    public static function hasUsableImage($photo, $gallery): bool
    {
        if ($photo && Storage::disk('public')->exists($photo)) {
            return true;
        }

        $gallery = is_string($gallery) ? json_decode($gallery, true) : $gallery;
        if (is_array($gallery)) {
            foreach ($gallery as $image) {
                if ($image && Storage::disk('public')->exists($image)) {
                    return true;
                }
            }
        }

        return false;
    }

    // Regras mínimas para o produto poder ficar ativo na loja
    public function meetsActiveRequirements(): bool
    {
        return !$this->is_outlet
            && static::hasUsableImage($this->photo, $this->gallery)
            && (float) $this->price >= self::MINIMUM_VISIBLE_PRICE
            && (int) $this->stock > 0
            && trim((string) $this->description) !== '';
    }

    public function scopeSellable(Builder $query): Builder
    {
        return $query
            ->where($query->getModel()->qualifyColumn('is_outlet'), false)
            ->where($query->getModel()->qualifyColumn('status'), 1)
            ->where($query->getModel()->qualifyColumn('stock'), '>', 0);
    }

    public function isSellable(): bool
    {
        return !$this->is_outlet && (int) $this->status === 1 && (int) $this->stock > 0;
    }

    public static function referenceParts(?string $value): array
    {
        $value = trim((string) $value);
        $reference = $value;
        $size = null;
        $color = null;

        // No cadastro do integrador, tamanho e cor normalmente chegam no fim:
        // "MODELO J05999 #12M *401". A cor é sempre o último parâmetro *... .
        if (preg_match('/\*\s*([^\s*#]+)\s*$/u', $reference, $matches)) {
            $color = trim($matches[1]);
            $reference = trim(substr($reference, 0, (int) strrpos($reference, '*')));
        }

        if (preg_match('/#\s*([^\s*#]+)\s*$/u', $reference, $matches)) {
            $size = trim($matches[1]);
            $reference = trim(substr($reference, 0, (int) strrpos($reference, '#')));
        }

        return [
            'reference' => $reference,
            'reference_key' => self::normalizeReferenceValue($reference),
            'size' => $size ?: null,
            'color' => $color ?: null,
            'color_key' => self::normalizeColorToken($color),
        ];
    }

    public function inferredSize(): ?string
    {
        return filled($this->size)
            ? trim((string) $this->size)
            : self::referenceParts($this->referenceSource())['size'];
    }

    public function referenceKey(): string
    {
        return self::referenceParts($this->referenceSource())['reference_key'];
    }

    public function referenceLabel(): string
    {
        return self::referenceParts($this->referenceSource())['reference'];
    }

    public function relationshipReferenceKey(): string
    {
        $referenceKey = $this->referenceKey();
        $tokens = preg_split('/\s+/', $referenceKey, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        foreach (array_reverse($tokens) as $token) {
            $hasLettersAndNumbers = preg_match('/[A-Z]/', $token) && preg_match('/\d/', $token);
            $isLongNumericReference = ctype_digit($token) && strlen($token) >= 5;

            if ($hasLettersAndNumbers || $isLongNumericReference) {
                return $token;
            }
        }

        return $referenceKey;
    }

    public function relationshipSearchTerm(): string
    {
        return $this->relationshipReferenceKey() ?: $this->referenceLabel();
    }

    public function inferredColorKey(): string
    {
        $inferred = self::referenceParts($this->referenceSource())['color_key'];

        return $inferred !== '' ? $inferred : self::normalizeColorToken($this->color);
    }

    public function inferredColorCode(): string
    {
        return trim((string) self::referenceParts($this->referenceSource())['color']);
    }

    public function relationshipColorKey(): string
    {
        $hex = strtoupper(trim((string) $this->color));

        return preg_match('/^#[0-9A-F]{6}$/', $hex)
            ? $hex
            : $this->inferredColorKey();
    }

    private function referenceSource(): string
    {
        $candidates = collect([$this->external_name, $this->name])
            ->filter(fn ($value) => filled($value))
            ->map(fn ($value) => trim((string) $value));

        return (string) $candidates
            ->sortByDesc(function (string $value) {
                $score = strlen($value);
                $score += str_contains($value, '*') ? 1000 : 0;
                $score += str_contains($value, '#') ? 500 : 0;
                $score += preg_match('/\b(?=[A-Z0-9-]*[A-Z])(?=[A-Z0-9-]*\d)[A-Z0-9-]{4,}\b/i', $value) ? 250 : 0;

                return $score;
            })
            ->first();
    }

    private static function normalizeReferenceValue(?string $value): string
    {
        return preg_replace('/[^A-Z0-9]+/', ' ', strtoupper(Str::ascii(trim((string) $value)))) ?: '';
    }

    private static function normalizeColorToken(?string $value): string
    {
        $token = preg_replace('/[^A-Z0-9]+/', '', strtoupper(Str::ascii(trim((string) $value)))) ?: '';

        return [
            'WHI' => 'WHITE', 'WHT' => 'WHITE', 'WHITE' => 'WHITE', 'BCO' => 'WHITE', 'BRANCO' => 'WHITE', 'BLANCO' => 'WHITE',
            'BLK' => 'BLACK', 'BLA' => 'BLACK', 'BLACK' => 'BLACK', 'PTO' => 'BLACK', 'PRETO' => 'BLACK', 'NEGRO' => 'BLACK',
            'BLU' => 'BLUE', 'BLUE' => 'BLUE', 'AZUL' => 'BLUE',
            'RED' => 'RED', 'VERMELHO' => 'RED', 'ROJO' => 'RED',
            'GRN' => 'GREEN', 'GREEN' => 'GREEN', 'VERDE' => 'GREEN',
            'YEL' => 'YELLOW', 'YELLOW' => 'YELLOW', 'AMARELO' => 'YELLOW', 'AMARILLO' => 'YELLOW',
            'GRY' => 'GREY', 'GRAY' => 'GREY', 'GREY' => 'GREY', 'CINZA' => 'GREY', 'GRIS' => 'GREY',
            'PNK' => 'PINK', 'PINK' => 'PINK', 'ROSA' => 'PINK',
            'BRN' => 'BROWN', 'BROWN' => 'BROWN', 'MARROM' => 'BROWN', 'MARRON' => 'BROWN',
            'BEI' => 'BEIGE', 'BEIGE' => 'BEIGE',
        ][$token] ?? $token;
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function translations()
    {
        return $this->hasMany(ProductTranslation::class, 'product_id', 'id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function categoriasFilhas()
    {
        return $this->belongsTo(CategoriasFilhas::class, 'childcategory_id');
    }

    // Ancla vertical de talla: `parent_id` apunta al producto base visible.
    public function parent()
    {
        return $this->belongsTo(Product::class, 'parent_id');
    }

    // Variantes verticales de talla.
    public function children()
    {
        return $this->hasMany(Product::class, 'parent_id');
    }

    // Relação com cupons aplicáveis a este produto
    public function cupons()
    {
        return $this->hasMany(Cupon::class, 'produto_id');
    }

    // Alias de variantes de talla.
    public function filhos()
    {
        return $this->hasMany(Product::class, 'parent_id', 'id');
    }

    // Alias del ancla de talla.
    public function pai()
    {
        return $this->belongsTo(Product::class, 'parent_id', 'id');
    }

    // Miembros de la familia de color. `color_parent_id` apunta al ancla de la familia.
    public function coresRelacionadas()
    {
        return $this->hasMany(Product::class, 'color_parent_id', 'id');
    }

    // Favoritado por usuários
    public function favoredByUsers()
    {
        return $this->belongsToMany(User::class, 'user_product_preferences')->withTimestamps();
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getResolvedCardColorsAttribute(): array
    {
        $colors = [];

        $pushColor = function ($value) use (&$colors) {
            $hex = strtoupper(trim((string) $value));
            if ($hex === '') {
                return;
            }

            if (!str_starts_with($hex, '#')) {
                $hex = '#' . $hex;
            }

            if (preg_match('/^#[0-9A-F]{6}$/', $hex)) {
                $colors[$hex] = $hex;
            }
        };

        $rawColors = $this->colors ?? null;
        if (is_string($rawColors)) {
            $decoded = json_decode($rawColors, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $rawColors = $decoded;
            } else {
                $rawColors = array_filter(array_map('trim', explode(',', $rawColors)));
            }
        }

        if (is_array($rawColors)) {
            foreach ($rawColors as $value) {
                $pushColor($value);
            }
        }

        $pushColor($this->color ?? null);

        if (self::$hasColorParentColumn === null) {
            $table = $this->getTable();
            self::$hasColorParentColumn = Schema::hasColumn($table, 'color_parent_id');
            self::$hasColorColumn = Schema::hasColumn($table, 'color');
        }

        if (!self::$hasColorParentColumn || !self::$hasColorColumn) {
            return array_values($colors);
        }

        $familyId = (int) ($this->color_parent_id ?: $this->id);
        if ($familyId <= 0) {
            return array_values($colors);
        }

        if (!isset(self::$familyColorCache[$familyId])) {
            $family = self::query()
                ->select(['id', 'color', 'color_parent_id'])
                ->where('status', 1)
                ->where('product_role', 'P')
                ->where(function ($q) use ($familyId) {
                    $q->where('id', $familyId)
                        ->orWhere('color_parent_id', $familyId);
                })
                ->get();

            $familyColors = [];
            foreach ($family as $variant) {
                $hex = strtoupper(trim((string) $variant->color));
                if ($hex === '') {
                    continue;
                }
                if (!str_starts_with($hex, '#')) {
                    $hex = '#' . $hex;
                }
                if (preg_match('/^#[0-9A-F]{6}$/', $hex)) {
                    $familyColors[$hex] = $hex;
                }
            }

            self::$familyColorCache[$familyId] = array_values($familyColors);
        }

        foreach (self::$familyColorCache[$familyId] as $hex) {
            $colors[$hex] = $hex;
        }

        return array_values($colors);
    }

    public $timestamps = true;
}
