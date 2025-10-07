<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeneralsettingsTable extends Migration
{
    public function up()
    {
        Schema::create('generalsettings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name')->nullable();
            // campos dos highlights já na criação
            $sections = [
                'destaque','mais_vendidos','melhores_avaliacoes','super_desconto',
                'famosos','lancamentos','tendencias','promocoes','ofertas_relampago','navbar'
            ];

            foreach ($sections as $section) {
                $table->boolean("show_highlight_$section")->default(1);
            }

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('generalsettings');
    }
}
