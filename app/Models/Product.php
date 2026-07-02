<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
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
    ];

    protected static ?bool $hasColorParentColumn = null;
    protected static ?bool $hasColorColumn = null;
    protected static array $familyColorCache = [];

    // URL da foto do produto
    public function getPhotoUrlAttribute()
    {
        if ($this->photo && Storage::disk('public')->exists($this->photo)) {
            return Storage::url($this->photo);
        }
        return asset('storage/uploads/noimage.webp');
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
