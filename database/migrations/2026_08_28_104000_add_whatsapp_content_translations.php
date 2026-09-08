<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const KEYS = [
        'whatsapp_admin_translations',
        'whatsapp_admin_english',
        'whatsapp_admin_spanish',
        'whatsapp_admin_delete_contact',
        'whatsapp_icon_service',
        'whatsapp_icon_floor',
        'whatsapp_icon_store',
        'whatsapp_icon_luxury',
        'whatsapp_icon_fashion',
        'whatsapp_icon_optical',
        'whatsapp_icon_shopping',
        'whatsapp_icon_food',
        'whatsapp_icon_delivery',
        'whatsapp_icon_location',
        'whatsapp_icon_returns',
        'whatsapp_icon_information',
        'contact_form_type',
        'contact_form_jobs_title',
        'contact_form_jobs_description',
    ];

    public function up(): void
    {
        Schema::table('whatsapp_widget_settings', function (Blueprint $table) {
            $table->string('title_en', 100)->nullable()->after('title');
            $table->string('title_es', 100)->nullable()->after('title_en');
            $table->string('subtitle_en', 180)->nullable()->after('subtitle');
            $table->string('subtitle_es', 180)->nullable()->after('subtitle_en');
        });

        Schema::table('whatsapp_contacts', function (Blueprint $table) {
            $table->string('title_en', 120)->nullable()->after('title');
            $table->string('title_es', 120)->nullable()->after('title_en');
            $table->string('category_en', 80)->nullable()->after('category');
            $table->string('category_es', 80)->nullable()->after('category_en');
            $table->text('message_en')->nullable()->after('message');
            $table->text('message_es')->nullable()->after('message_en');
            $table->string('description_en', 240)->nullable()->after('description');
            $table->string('description_es', 240)->nullable()->after('description_en');
        });

        DB::table('whatsapp_widget_settings')->update([
            'title_en' => 'SAX Digital Concierge',
            'title_es' => 'Concierge Digital SAX',
            'subtitle_en' => 'Choose an option and talk to our team.',
            'subtitle_es' => 'Elige una opción y habla con nuestro equipo.',
        ]);

        $contacts = [
            'Consultoria de produto' => [
                'Product consultation', 'Consultoría de producto',
                'Specialists', 'Especialistas',
                'Hello! I would like a product consultation.', '¡Hola! Me gustaría recibir una consultoría de producto.',
                'Personalized guidance to find products, brands and collections.', 'Orientación personalizada para encontrar productos, marcas y colecciones.',
            ],
            'Informações de envio e frete' => [
                'Shipping and delivery information', 'Información de envío y entrega',
                'Delivery', 'Entregas',
                'Hello! I would like information about shipping and delivery.', '¡Hola! Quisiera información sobre envío y entrega.',
                'Delivery times, shipping methods and order delivery information.', 'Plazos, modalidades de envío e información sobre la entrega del pedido.',
            ],
            'Serviço de Personal Shopper' => [
                'Personal Shopper service', 'Servicio de Personal Shopper',
                'Service', 'Atención',
                'Hello! I would like to speak with a Personal Shopper.', '¡Hola! Quisiera hablar con un Personal Shopper.',
                'Exclusive support for a personalized shopping experience.', 'Acompañamiento exclusivo para una experiencia de compra personalizada.',
            ],
            'Trocas e pós-venda' => [
                'Exchanges and after-sales', 'Cambios y posventa',
                'After-sales', 'Posventa',
                'Hello! I need help with an exchange or after-sales.', '¡Hola! Necesito ayuda con un cambio o posventa.',
                'Discreet support for exchanges, returns and post-purchase care.', 'Soporte discreto para cambios, devoluciones y atención después de la compra.',
            ],
            'Rastrear pedido' => [
                'Track order', 'Rastrear pedido',
                'Orders', 'Pedidos',
                'Hello! I would like to track my order.', '¡Hola! Quisiera rastrear mi pedido.',
                'Track your order progress and location.', 'Consulta el estado y la ubicación de tu pedido.',
            ],
        ];

        foreach ($contacts as $title => $values) {
            DB::table('whatsapp_contacts')->where('title', $title)->update([
                'title_en' => $values[0],
                'title_es' => $values[1],
                'category_en' => $values[2],
                'category_es' => $values[3],
                'message_en' => $values[4],
                'message_es' => $values[5],
                'description_en' => $values[6],
                'description_es' => $values[7],
            ]);
        }

        $translations = [
            ['whatsapp_admin_translations', 'Traduções do conteúdo', 'Content translations', 'Traducciones del contenido'],
            ['whatsapp_admin_english', 'Versão em inglês', 'English version', 'Versión en inglés'],
            ['whatsapp_admin_spanish', 'Versão em espanhol', 'Spanish version', 'Versión en español'],
            ['whatsapp_admin_delete_contact', 'Excluir contato', 'Delete contact', 'Eliminar contacto'],
            ['whatsapp_icon_service', 'Atendimento', 'Customer service', 'Atención'],
            ['whatsapp_icon_floor', 'Piso / edifício', 'Floor / building', 'Piso / edificio'],
            ['whatsapp_icon_store', 'Loja', 'Store', 'Tienda'],
            ['whatsapp_icon_luxury', 'Luxo / joias', 'Luxury / jewelry', 'Lujo / joyas'],
            ['whatsapp_icon_fashion', 'Moda', 'Fashion', 'Moda'],
            ['whatsapp_icon_optical', 'Ótica', 'Optical', 'Óptica'],
            ['whatsapp_icon_shopping', 'Compras', 'Shopping', 'Compras'],
            ['whatsapp_icon_food', 'Gastronomia', 'Food and dining', 'Gastronomía'],
            ['whatsapp_icon_delivery', 'Entregas', 'Delivery', 'Entregas'],
            ['whatsapp_icon_location', 'Localização / pedidos', 'Location / orders', 'Ubicación / pedidos'],
            ['whatsapp_icon_returns', 'Trocas / pós-venda', 'Exchanges / after-sales', 'Cambios / posventa'],
            ['whatsapp_icon_information', 'Informações', 'Information', 'Información'],
            ['contact_form_type', 'Tipo de contato', 'Contact type', 'Tipo de contacto'],
            ['contact_form_jobs_title', 'Vagas em destaque', 'Featured openings', 'Vacantes destacadas'],
            ['contact_form_jobs_description', 'Confira nossas oportunidades', 'View our opportunities', 'Conoce nuestras oportunidades'],
        ];

        foreach ($translations as $translation) {
            DB::table('languages')->updateOrInsert(
                ['key' => $translation[0]],
                [
                    'pt' => $translation[1],
                    'en' => $translation[2],
                    'es' => $translation[3],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('languages')->whereIn('key', self::KEYS)->delete();

        Schema::table('whatsapp_contacts', function (Blueprint $table) {
            $table->dropColumn([
                'title_en', 'title_es', 'category_en', 'category_es',
                'message_en', 'message_es', 'description_en', 'description_es',
            ]);
        });

        Schema::table('whatsapp_widget_settings', function (Blueprint $table) {
            $table->dropColumn(['title_en', 'title_es', 'subtitle_en', 'subtitle_es']);
        });
    }
};
