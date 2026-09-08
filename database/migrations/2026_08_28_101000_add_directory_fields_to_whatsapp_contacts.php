<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_contacts', function (Blueprint $table) {
            $table->string('category', 80)->default('Atendimento')->after('title');
            $table->string('icon', 50)->default('fa-headset')->after('category');
            $table->string('description', 240)->nullable()->after('message');
            $table->boolean('show_on_contact_page')->default(true)->after('page_contexts');
            $table->index(['show_on_contact_page', 'active'], 'whatsapp_contacts_directory_index');
        });

        $defaults = [
            'Consultoria de produto' => ['Especialistas', 'fa-gem', 'Orientação personalizada para encontrar produtos, marcas e coleções.'],
            'Informações de envio e frete' => ['Entregas', 'fa-truck-fast', 'Prazos, modalidades de envio e informações sobre a entrega do pedido.'],
            'Serviço de Personal Shopper' => ['Atendimento', 'fa-bag-shopping', 'Acompanhamento exclusivo para uma experiência de compra personalizada.'],
            'Trocas e pós-venda' => ['Pós-venda', 'fa-rotate-left', 'Suporte discreto para trocas, devoluções e cuidados depois da compra.'],
            'Rastrear pedido' => ['Pedidos', 'fa-location-dot', 'Acompanhe o andamento e a localização do seu pedido.'],
        ];

        foreach ($defaults as $title => [$category, $icon, $description]) {
            DB::table('whatsapp_contacts')->where('title', $title)->update([
                'category' => $category,
                'icon' => $icon,
                'description' => $description,
                'show_on_contact_page' => true,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('whatsapp_contacts', function (Blueprint $table) {
            $table->dropIndex('whatsapp_contacts_directory_index');
            $table->dropColumn(['category', 'icon', 'description', 'show_on_contact_page']);
        });
    }
};
