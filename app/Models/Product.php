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
        'mercadolivre_name',
        'mercadolivre_description',
        'mercadolivre_id',
        'mercadolivre_category_attributes',
        'mercadolivre_listing_type_id',
        'mercadolivre_price',
        'mercadolivre_warranty_type_id',
        'mercadolivre_warranty_type_name',
        'mercadolivre_warranty_time',
        'mercadolivre_warranty_time_unit',
        'mercadolivre_without_warranty',
        'show_in_navbar',
        'product_size',
        'synced',
        'gtin',
        'promotion_price',
        'name',
        'description',
        'price',
        'stock',
        'brand_id',
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

    // Relação com cupons aplicáveis a este produto
    public function cupons()
    {
        return $this->hasMany(Cupon::class, 'produto_id');
    }

    public function childcategory()
    {
        return $this->belongsTo(Childcategory::class);
    }

    // Product.php
    public function favoredByUsers()
    {
        return $this->belongsToMany(User::class, 'user_product_preferences')->withTimestamps();
    }

    public $timestamps = true;
}
