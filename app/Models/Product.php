<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'price',
        'previous_price',
        'stock',
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
        'brand_id',
        'ref_code',
        'ref_code_int',
        'mpn',
        'free_shipping',
        'max_quantity',
        'weight',
        'width',
        'height',
        'length',
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
    ];

    // No modelo Product
    public function uploads()
    {
        return $this->hasMany(Upload::class, 'product_id'); // Supondo que a chave estrangeira seja 'product_id'
    }

    public $timestamps = true;
}
