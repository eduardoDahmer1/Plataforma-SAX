<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{

    public function up()
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ex: Bancard, Pagopar, etc
            $table->text('description')->nullable();
            $table->string('type')->default('gateway'); // ou 'bank', 'cash'
            $table->json('credentials')->nullable(); // json com keys (public, private, etc)
            $table->boolean('sandbox')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }
    protected $fillable = [
        'type',
        'name',
        'description',
        'settings',
        'credentials', // adiciona aqui
        'active'
    ];

    protected $casts = [
        'settings' => 'array',
        'credentials' => 'array', // transforma automaticamente JSON em array PHP e vice-versa
        'active' => 'boolean'
    ];
}
