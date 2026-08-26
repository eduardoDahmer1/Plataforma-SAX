<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $columns = [
        'header_categories_enabled', 'header_institucional_enabled', 'header_bridal_enabled',
        'header_palace_enabled', 'header_cafe_enabled', 'header_blog_enabled', 'header_contact_enabled',
        'footer_categories_enabled', 'footer_institucional_enabled', 'footer_bridal_enabled',
        'footer_palace_enabled', 'footer_cafe_enabled', 'footer_blog_enabled', 'footer_contact_enabled',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('system_settings')) {
            return;
        }

        Schema::table('system_settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('system_settings', 'store_profile')) {
                $table->string('store_profile')->default('stage')->after('maintenance');
            }

            foreach ($this->columns as $column) {
                if (! Schema::hasColumn('system_settings', $column)) {
                    $table->boolean($column)->default(true);
                }
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('system_settings')) {
            return;
        }

        Schema::table('system_settings', function (Blueprint $table): void {
            foreach (array_merge(['store_profile'], $this->columns) as $column) {
                if (Schema::hasColumn('system_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
