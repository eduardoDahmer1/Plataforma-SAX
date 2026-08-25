<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dhl_settings', function (Blueprint $table): void {
            $table->id();
            $table->boolean('enabled')->default(false);
            $table->string('environment', 20)->default('sandbox');
            $table->text('api_key')->nullable();
            $table->text('api_secret')->nullable();
            $table->text('account_number')->nullable();

            $table->string('currency', 3)->default('USD');
            $table->string('unit_of_measurement', 20)->default('metric');
            $table->string('duties_taxes_payer', 20)->default('receiver');
            $table->string('incoterm', 3)->default('DAP');

            $table->string('origin_country_code', 2)->default('PY');
            $table->string('origin_postal_code', 30)->nullable();
            $table->string('origin_city_name', 120)->nullable();
            $table->string('origin_province_code', 20)->nullable();
            $table->string('origin_province_name', 120)->nullable();
            $table->string('origin_district_name', 120)->nullable();
            $table->string('origin_address_line_1')->nullable();
            $table->string('origin_address_line_2')->nullable();
            $table->string('origin_company_name')->nullable();
            $table->string('origin_trading_name')->nullable();
            $table->string('origin_tax_id', 40)->nullable();
            $table->string('origin_contact_name', 120)->nullable();
            $table->string('origin_phone', 40)->nullable();
            $table->string('origin_email')->nullable();

            $table->decimal('test_weight_kg', 10, 3)->default(1);
            $table->decimal('test_length_cm', 10, 2)->default(10);
            $table->decimal('test_width_cm', 10, 2)->default(10);
            $table->decimal('test_height_cm', 10, 2)->default(10);
            $table->decimal('test_declared_value', 12, 2)->default(100);
            $table->string('test_destination_country_code', 2)->default('US');
            $table->string('test_destination_postal_code', 30)->default('10001');
            $table->string('test_destination_city', 120)->default('New York');

            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dhl_settings');
    }
};
