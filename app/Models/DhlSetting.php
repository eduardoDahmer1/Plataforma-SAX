<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DhlSetting extends Model
{
    protected $fillable = [
        'enabled', 'environment', 'api_key', 'api_secret', 'account_number',
        'currency', 'unit_of_measurement', 'duties_taxes_payer', 'incoterm',
        'excluded_country_codes',
        'volumetric_divisor', 'rate_markup_percent', 'rate_markup_enabled', 'fallback_measurements_enabled',
        'free_shipping_enabled', 'free_shipping_min_items', 'free_shipping_min_subtotal',
        'free_shipping_max_billable_weight_kg',
        'origin_country_code', 'origin_postal_code', 'origin_city_name',
        'origin_province_code', 'origin_province_name', 'origin_district_name',
        'origin_address_line_1', 'origin_address_line_2', 'origin_company_name',
        'origin_trading_name', 'origin_tax_id', 'origin_contact_name',
        'origin_phone', 'origin_email',
        'test_package_id',
        'test_weight_kg', 'test_length_cm', 'test_width_cm', 'test_height_cm',
        'test_declared_value', 'test_destination_country_code',
        'test_destination_province_code', 'test_destination_province_name',
        'test_destination_postal_code', 'test_destination_city', 'updated_by',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'api_key' => 'encrypted',
        'api_secret' => 'encrypted',
        'account_number' => 'encrypted',
        'excluded_country_codes' => 'array',
        'volumetric_divisor' => 'float',
        'rate_markup_percent' => 'float',
        'rate_markup_enabled' => 'boolean',
        'fallback_measurements_enabled' => 'boolean',
        'free_shipping_enabled' => 'boolean',
        'free_shipping_min_items' => 'integer',
        'free_shipping_min_subtotal' => 'float',
        'free_shipping_max_billable_weight_kg' => 'float',
        'test_package_id' => 'integer',
        'test_weight_kg' => 'decimal:3',
        'test_length_cm' => 'decimal:2',
        'test_width_cm' => 'decimal:2',
        'test_height_cm' => 'decimal:2',
        'test_declared_value' => 'decimal:2',
    ];

    protected $hidden = [
        'api_key',
        'api_secret',
        'account_number',
    ];
}
