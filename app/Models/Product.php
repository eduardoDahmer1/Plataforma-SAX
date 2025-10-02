<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
        'synced',
        'gtin',
        'promotion_price',
        'name',
        'description',
        'price',
        'color',
        'size',
        'stock',
        'brand_id',
        'parent_id',
        'color_parent_id', // adiciona a coluna que vai armazenar cores como array
    ];

    // Casts para JSON/array
    protected $casts = [
        'gallery' => 'array',
        'parent_id' => 'array',
        'color_parent_id' => 'array',
        'price' => 'float',
        'stock' => 'integer',
    ];

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

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function childcategory()
    {
        return $this->belongsTo(Childcategory::class);
    }

    // Produto Pai
    public function parent()
    {
        return $this->belongsTo(Product::class, 'parent_id');
    }

    // Produtos Filhos
    public function children()
    {
        return $this->hasMany(Product::class, 'parent_id');
    }

    // Relação com cupons aplicáveis a este produto
    public function cupons()
    {
        return $this->hasMany(Cupon::class, 'produto_id');
    }

    // Produto pai -> filhos
    public function filhos()
    {
        return $this->hasMany(Product::class, 'parent_id', 'id');
    }

    // Produto filho -> pai
    public function pai()
    {
        return $this->belongsTo(Product::class, 'parent_id', 'id');
    }

    public function coresRelacionadas()
    {
        return $this->hasMany(Product::class, 'id', 'color_parent_id');
    }

    // Favoritado por usuários
    public function favoredByUsers()
    {
        return $this->belongsToMany(User::class, 'user_product_preferences')->withTimestamps();
    }

    public $timestamps = true;
}
