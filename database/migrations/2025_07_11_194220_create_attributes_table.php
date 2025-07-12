<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('attributable_id')->nullable();
            $table->string('attributable_type', 255)->nullable();
            $table->string('input_name', 255)->nullable();
            $table->integer('price_status')->default(1)->comment('0 - hide, 1 - show');
            $table->tinyInteger('show_price')->default(0);
            $table->integer('details_status')->default(1)->comment('0 - hide, 1 - show');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attributes');
    }
};
