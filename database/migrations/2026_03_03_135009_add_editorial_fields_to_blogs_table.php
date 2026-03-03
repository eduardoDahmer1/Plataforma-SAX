<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            // Adicionando os novos campos após colunas existentes para manter a ordem
            $table->integer('read_time')->nullable()->after('title');
            $table->string('image_caption')->nullable()->after('image');
            $table->text('meta_description')->nullable()->after('content');
            $table->boolean('featured')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->dropColumn(['read_time', 'image_caption', 'meta_description', 'featured']);
        });
    }
};