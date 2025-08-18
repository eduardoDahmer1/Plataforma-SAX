<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDetailsColumnsToUsersTable extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone_country', 2)->nullable(); // BR ou PY
            $table->string('phone_number')->nullable();
            $table->string('location_country', 2)->nullable(); // BR ou PY
            $table->string('address')->nullable(); // Endereço completo
            $table->string('cep')->nullable(); // só BR
            $table->string('state')->nullable(); // só BR
            $table->string('city')->nullable(); // só BR
            $table->boolean('already_registered')->default(false); // Já tem cadastro na SAX?
            $table->text('additional_info')->nullable(); // Para detalhes do Paraguai ou dados extras
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone_country', 'phone_number',
                'location_country', 'address',
                'cep', 'state', 'city',
                'already_registered', 'additional_info'
            ]);
        });
    }
}
