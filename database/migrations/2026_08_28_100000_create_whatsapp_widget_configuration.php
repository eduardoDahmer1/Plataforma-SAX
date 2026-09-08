<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_widget_settings', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('Concierge Digital SAX');
            $table->string('subtitle')->default('Escolha uma opção e fale com nosso time.');
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('whatsapp_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('phone', 30);
            $table->text('message')->nullable();
            $table->json('page_contexts');
            $table->boolean('active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['active', 'sort_order']);
        });

        DB::table('whatsapp_widget_settings')->insert([
            'title' => 'Concierge Digital SAX',
            'subtitle' => 'Escolha uma opção e fale com nosso time.',
            'enabled' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $phone = '+595 984 167575';
        $contacts = [
            ['Consultoria de produto', 'Olá! Gostaria de receber uma consultoria de produto.'],
            ['Informações de envio e frete', 'Olá! Gostaria de informações sobre envio e frete.'],
            ['Serviço de Personal Shopper', 'Olá! Gostaria de falar com o serviço de Personal Shopper.'],
            ['Trocas e pós-venda', 'Olá! Preciso de ajuda com trocas ou pós-venda.'],
            ['Rastrear pedido', 'Olá! Gostaria de rastrear meu pedido.'],
        ];

        DB::table('whatsapp_contacts')->insert(array_map(
            static fn (array $contact, int $index): array => [
                'title' => $contact[0],
                'phone' => $phone,
                'message' => $contact[1],
                'page_contexts' => json_encode(['all']),
                'active' => true,
                'sort_order' => ($index + 1) * 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            $contacts,
            array_keys($contacts)
        ));
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_contacts');
        Schema::dropIfExists('whatsapp_widget_settings');
    }
};
