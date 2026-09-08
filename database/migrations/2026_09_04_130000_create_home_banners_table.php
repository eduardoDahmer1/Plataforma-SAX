<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_banners', function (Blueprint $table) {
            $table->id();
            $table->string('group', 30);
            $table->string('image');
            $table->text('link')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['group', 'is_active', 'sort_order']);
        });

        if (! Schema::hasTable('attributes')) {
            return;
        }

        $attribute = DB::table('attributes')->orderBy('id')->first();
        if (! $attribute) {
            return;
        }

        $now = now();
        $rows = [];
        foreach (['main' => range(1, 5), 'editorial' => range(6, 8)] as $group => $indexes) {
            foreach ($indexes as $position => $index) {
                $imageField = "banner{$index}";
                $linkField = "banner{$index}_link";
                $image = $attribute->{$imageField} ?? null;
                if (! filled($image)) {
                    continue;
                }

                $rows[] = [
                    'group' => $group,
                    'image' => $image,
                    'link' => $attribute->{$linkField} ?? null,
                    'sort_order' => $position,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if ($rows !== []) {
            DB::table('home_banners')->insert($rows);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('home_banners');
    }
};
