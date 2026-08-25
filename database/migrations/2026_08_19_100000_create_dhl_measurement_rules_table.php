<?php

use App\Services\Dhl\DhlMeasurementRuleDefaults;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dhl_measurement_rules', function (Blueprint $table): void {
            $table->id();
            $table->string('hierarchy_key', 50)->unique();
            $table->string('level', 20)->index();
            $table->unsignedBigInteger('category_id')->index();
            $table->unsignedBigInteger('subcategory_id')->nullable()->index();
            $table->unsignedBigInteger('childcategory_id')->nullable()->index();
            $table->string('scope_name', 255);
            $table->string('profile_code', 40);
            $table->boolean('active')->default(true);
            $table->boolean('requires_manual_review')->default(false);
            $table->decimal('weight_kg', 10, 3);
            $table->decimal('length_cm', 10, 2);
            $table->decimal('width_cm', 10, 2);
            $table->decimal('height_cm', 10, 2);
            $table->timestamps();
        });

        app(DhlMeasurementRuleDefaults::class)->sync();
    }

    public function down(): void
    {
        Schema::dropIfExists('dhl_measurement_rules');
    }
};
