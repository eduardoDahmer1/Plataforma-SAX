<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const KEYS = [
        'contact_guide_nav_general', 'contact_guide_nav_optical', 'contact_guide_eyebrow_general',
        'contact_guide_eyebrow_optical', 'contact_guide_title_general', 'contact_guide_title_optical',
        'contact_guide_description_general', 'contact_guide_description_optical', 'contact_guide_units',
        'contact_guide_sectors', 'contact_guide_brands', 'contact_guide_search_placeholder',
        'contact_guide_filter_cities', 'contact_guide_all_cities', 'contact_guide_optical_badge',
        'contact_guide_brands_label', 'contact_guide_contact_updating', 'contact_guide_talk_now',
        'contact_guide_empty_title', 'contact_guide_empty_description', 'contact_guide_help_eyebrow',
        'contact_guide_help_title', 'contact_guide_help_button', 'contact_form_guide_general',
        'contact_form_guide_optical', 'contact_form_validation_title', 'contact_form_direct_eyebrow',
        'contact_form_direct_title', 'contact_form_show_contacts', 'contact_form_hide_contacts',
        'contact_form_search_contacts', 'contact_form_filter_contacts', 'contact_form_all',
        'contact_form_no_contacts', 'contact_form_optical_eyebrow', 'contact_form_optical_title',
        'contact_form_optical_description', 'contact_form_prescription_label', 'contact_form_select_prescription',
        'contact_form_file_hint', 'contact_form_no_file', 'contact_form_describe_need',
        'contact_form_optical_placeholder', 'whatsapp_speak_with', 'whatsapp_open_service',
        'admin_guide_menu', 'admin_guide_public_content', 'admin_guide_title', 'admin_guide_description',
        'admin_guide_view_site', 'admin_guide_new_content', 'admin_guide_add_sector',
        'admin_guide_location', 'admin_guide_floor', 'admin_guide_sector', 'admin_guide_order',
        'admin_guide_phone', 'admin_guide_custom_link', 'admin_guide_optional',
        'admin_guide_short_description', 'admin_guide_brands_hint', 'admin_guide_show_optical',
        'admin_guide_registered_structure', 'admin_guide_count_summary', 'admin_guide_new_location',
        'admin_guide_city', 'admin_guide_location_name', 'admin_guide_complement', 'admin_guide_hours',
        'admin_guide_hours_hint', 'admin_guide_create_location', 'admin_guide_save_location',
        'admin_guide_no_phone', 'admin_guide_custom_link_short', 'admin_guide_description_field',
        'admin_guide_save_sector', 'admin_guide_delete_sector', 'admin_guide_delete_location',
        'admin_guide_confirm_delete_sector', 'admin_guide_confirm_delete_location',
        'whatsapp_admin_title', 'whatsapp_admin_description', 'whatsapp_admin_guide_button',
        'whatsapp_admin_edit_icon', 'whatsapp_admin_info', 'whatsapp_admin_menu_header',
        'whatsapp_admin_presentation', 'whatsapp_admin_enabled', 'whatsapp_admin_disabled',
        'whatsapp_admin_main_title', 'whatsapp_admin_support_text', 'whatsapp_admin_show_site',
        'whatsapp_admin_save_presentation', 'whatsapp_admin_new_destination', 'whatsapp_admin_add_number',
        'whatsapp_admin_number_title', 'whatsapp_admin_category', 'whatsapp_admin_number_ddi',
        'whatsapp_admin_order', 'whatsapp_admin_active', 'whatsapp_admin_short_description',
        'whatsapp_admin_prefilled_message', 'whatsapp_admin_contact_icon', 'whatsapp_admin_show_contact_page',
        'whatsapp_admin_pages_question', 'whatsapp_admin_add_contact', 'whatsapp_admin_destinations',
        'whatsapp_admin_contacts_count', 'whatsapp_admin_order_hint', 'whatsapp_admin_test_redirect',
        'whatsapp_admin_save_contact', 'whatsapp_admin_delete_confirm', 'whatsapp_admin_empty_title',
        'whatsapp_admin_empty_text', 'whatsapp_admin_success_settings', 'whatsapp_admin_success_created',
        'whatsapp_admin_success_updated', 'whatsapp_admin_success_deleted', 'whatsapp_admin_invalid_phone',
        'whatsapp_context_all', 'whatsapp_context_home', 'whatsapp_context_catalog', 'whatsapp_context_product',
        'whatsapp_context_cart', 'whatsapp_context_checkout', 'whatsapp_context_account', 'whatsapp_context_blog',
        'whatsapp_context_institutional', 'whatsapp_context_bridal', 'whatsapp_context_palace',
        'whatsapp_context_cafe', 'whatsapp_context_other', 'admin_guide_success_location_created',
        'admin_guide_success_location_updated', 'admin_guide_success_location_deleted',
        'admin_guide_success_sector_created', 'admin_guide_success_sector_updated',
        'admin_guide_success_sector_deleted',
    ];

    public function up(): void
    {
        Schema::table('contact_guide_locations', function (Blueprint $table) {
            $table->string('city_en', 100)->nullable()->after('city');
            $table->string('city_es', 100)->nullable()->after('city_en');
            $table->string('name_en', 140)->nullable()->after('name');
            $table->string('name_es', 140)->nullable()->after('name_en');
            $table->string('subtitle_en', 180)->nullable()->after('subtitle');
            $table->string('subtitle_es', 180)->nullable()->after('subtitle_en');
            $table->text('service_hours_en')->nullable()->after('service_hours');
            $table->text('service_hours_es')->nullable()->after('service_hours_en');
        });

        Schema::table('contact_guide_entries', function (Blueprint $table) {
            $table->string('floor_en', 80)->nullable()->after('floor');
            $table->string('floor_es', 80)->nullable()->after('floor_en');
            $table->string('sector_en', 160)->nullable()->after('sector');
            $table->string('sector_es', 160)->nullable()->after('sector_en');
            $table->string('description_en', 500)->nullable()->after('description');
            $table->string('description_es', 500)->nullable()->after('description_en');
        });

        $this->translateGuideData();

        $translations = [
            ['contact_guide_nav_general', 'Guia de setores', 'Department guide', 'Guía de sectores'],
            ['contact_guide_nav_optical', 'Guia de lentes', 'Eyewear guide', 'Guía de lentes'],
            ['contact_guide_eyebrow_general', 'Guia de marcas e setores', 'Brand and department guide', 'Guía de marcas y sectores'],
            ['contact_guide_eyebrow_optical', 'Atendimento óptico SAX', 'SAX optical service', 'Atención óptica SAX'],
            ['contact_guide_title_general', 'Tudo o que você procura, piso a piso', 'Everything you need, floor by floor', 'Todo lo que buscas, piso por piso'],
            ['contact_guide_title_optical', 'Encontre nossos especialistas em lentes', 'Find our eyewear specialists', 'Encuentra a nuestros especialistas en lentes'],
            ['contact_guide_description_general', 'Explore unidades, pisos, setores e marcas. Quando precisar, fale diretamente com a equipe responsável pelo WhatsApp.', 'Explore locations, floors, departments and brands. When needed, contact the right team directly through WhatsApp.', 'Explora sucursales, pisos, sectores y marcas. Cuando lo necesites, habla directamente con el equipo responsable por WhatsApp.'],
            ['contact_guide_description_optical', 'Consulte as unidades com atendimento óptico e fale diretamente com nossa equipe especializada.', 'Find locations with optical service and speak directly with our specialized team.', 'Consulta las sucursales con atención óptica y habla directamente con nuestro equipo especializado.'],
            ['contact_guide_units', 'unidades', 'locations', 'sucursales'],
            ['contact_guide_sectors', 'setores', 'departments', 'sectores'],
            ['contact_guide_brands', 'marcas', 'brands', 'marcas'],
            ['contact_guide_search_placeholder', 'Buscar marca, setor ou piso...', 'Search brand, department or floor...', 'Buscar marca, sector o piso...'],
            ['contact_guide_filter_cities', 'Filtrar por cidade', 'Filter by city', 'Filtrar por ciudad'],
            ['contact_guide_all_cities', 'Todas', 'All', 'Todas'],
            ['contact_guide_optical_badge', 'Ótica', 'Optical', 'Óptica'],
            ['contact_guide_brands_label', 'Marcas do setor', 'Department brands', 'Marcas del sector'],
            ['contact_guide_contact_updating', 'Contato em atualização', 'Contact being updated', 'Contacto en actualización'],
            ['contact_guide_talk_now', 'Falar agora', 'Chat now', 'Hablar ahora'],
            ['contact_guide_empty_title', 'Nenhum resultado encontrado', 'No results found', 'No se encontraron resultados'],
            ['contact_guide_empty_description', 'Tente buscar por outro setor, piso ou marca.', 'Try another department, floor or brand.', 'Prueba con otro sector, piso o marca.'],
            ['contact_guide_help_eyebrow', 'Ainda precisa de ajuda?', 'Still need help?', '¿Aún necesitas ayuda?'],
            ['contact_guide_help_title', 'Nossa equipe encontra o contato certo para você.', 'Our team will find the right contact for you.', 'Nuestro equipo encontrará el contacto correcto para ti.'],
            ['contact_guide_help_button', 'Ir para atendimento', 'Go to customer service', 'Ir a atención'],
            ['contact_form_guide_general', 'Consultar guia de setores e marcas', 'View department and brand guide', 'Consultar guía de sectores y marcas'],
            ['contact_form_guide_optical', 'Consultar guia de lentes', 'View eyewear guide', 'Consultar guía de lentes'],
            ['contact_form_validation_title', 'Revise as informações do formulário.', 'Please review the form information.', 'Revisa la información del formulario.'],
            ['contact_form_direct_eyebrow', 'Atendimento direto', 'Direct assistance', 'Atención directa'],
            ['contact_form_direct_title', 'Encontre o contato certo', 'Find the right contact', 'Encuentra el contacto correcto'],
            ['contact_form_show_contacts', 'Mostrar contatos', 'Show contacts', 'Mostrar contactos'],
            ['contact_form_hide_contacts', 'Ocultar contatos', 'Hide contacts', 'Ocultar contactos'],
            ['contact_form_search_contacts', 'Buscar contato, piso ou área...', 'Search contact, floor or area...', 'Buscar contacto, piso o área...'],
            ['contact_form_filter_contacts', 'Filtrar contatos por categoria', 'Filter contacts by category', 'Filtrar contactos por categoría'],
            ['contact_form_all', 'Todos', 'All', 'Todos'],
            ['contact_form_no_contacts', 'Nenhum contato encontrado nesse filtro.', 'No contacts found for this filter.', 'No se encontraron contactos con este filtro.'],
            ['contact_form_optical_eyebrow', 'Atendimento especializado', 'Specialized service', 'Atención especializada'],
            ['contact_form_optical_title', 'Fale com a Ótica SAX', 'Talk to SAX Optical', 'Habla con Óptica SAX'],
            ['contact_form_optical_description', 'Envie sua receita e conte brevemente como podemos ajudar.', 'Send your prescription and briefly tell us how we can help.', 'Envía tu receta y cuéntanos brevemente cómo podemos ayudarte.'],
            ['contact_form_prescription_label', 'Insira sua receita (opcional)', 'Upload your prescription (optional)', 'Adjunta tu receta (opcional)'],
            ['contact_form_select_prescription', 'Selecionar receita', 'Select prescription', 'Seleccionar receta'],
            ['contact_form_file_hint', 'PDF, JPG ou PNG · até 5 MB', 'PDF, JPG or PNG · up to 5 MB', 'PDF, JPG o PNG · hasta 5 MB'],
            ['contact_form_no_file', 'Nenhum arquivo selecionado', 'No file selected', 'Ningún archivo seleccionado'],
            ['contact_form_describe_need', 'Descreva o que você precisa', 'Describe what you need', 'Describe lo que necesitas'],
            ['contact_form_optical_placeholder', 'Ex.: preciso de lentes progressivas, armação leve ou orientação sobre minha receita.', 'E.g.: I need progressive lenses, a lightweight frame or help understanding my prescription.', 'Ej.: necesito lentes progresivos, una montura ligera u orientación sobre mi receta.'],
            ['whatsapp_speak_with', 'Falar com :name pelo WhatsApp', 'Chat with :name on WhatsApp', 'Hablar con :name por WhatsApp'],
            ['whatsapp_open_service', 'Abrir atendimento no WhatsApp', 'Open WhatsApp assistance', 'Abrir atención por WhatsApp'],
            ['admin_guide_menu', 'Guia de atendimento', 'Service guide', 'Guía de atención'],
            ['admin_guide_public_content', 'Conteúdo público', 'Public content', 'Contenido público'],
            ['admin_guide_title', 'Guia de setores e lentes', 'Department and eyewear guide', 'Guía de sectores y lentes'],
            ['admin_guide_description', 'Gerencie unidades, pisos, marcas e os contatos exibidos no guia. Itens marcados como Ótica são os únicos mostrados na loja de lentes.', 'Manage locations, floors, brands and contacts shown in the guide. Items marked Optical are the only ones displayed in the eyewear store.', 'Gestiona sucursales, pisos, marcas y contactos del guía. Solo los elementos marcados como Óptica aparecen en la tienda de lentes.'],
            ['admin_guide_view_site', 'Ver no site', 'View on site', 'Ver en el sitio'],
            ['admin_guide_new_content', 'Novo conteúdo', 'New content', 'Nuevo contenido'],
            ['admin_guide_add_sector', 'Adicionar setor', 'Add department', 'Agregar sector'],
            ['admin_guide_location', 'Unidade', 'Location', 'Sucursal'],
            ['admin_guide_floor', 'Piso', 'Floor', 'Piso'],
            ['admin_guide_sector', 'Setor', 'Department', 'Sector'],
            ['admin_guide_order', 'Ordem', 'Order', 'Orden'],
            ['admin_guide_phone', 'Telefone / WhatsApp', 'Phone / WhatsApp', 'Teléfono / WhatsApp'],
            ['admin_guide_custom_link', 'Link personalizado do WhatsApp', 'Custom WhatsApp link', 'Enlace personalizado de WhatsApp'],
            ['admin_guide_optional', '(opcional)', '(optional)', '(opcional)'],
            ['admin_guide_short_description', 'Explicação discreta', 'Short description', 'Explicación breve'],
            ['admin_guide_brands_hint', 'uma por linha ou separadas por vírgula', 'one per line or separated by commas', 'una por línea o separadas por comas'],
            ['admin_guide_show_optical', 'Mostrar na Ótica', 'Show in Optical', 'Mostrar en Óptica'],
            ['admin_guide_registered_structure', 'Estrutura cadastrada', 'Registered structure', 'Estructura registrada'],
            ['admin_guide_count_summary', ':locations unidades · :sectors setores', ':locations locations · :sectors departments', ':locations sucursales · :sectors sectores'],
            ['admin_guide_new_location', 'Nova unidade', 'New location', 'Nueva sucursal'],
            ['admin_guide_city', 'Cidade', 'City', 'Ciudad'],
            ['admin_guide_location_name', 'Nome da unidade', 'Location name', 'Nombre de la sucursal'],
            ['admin_guide_complement', 'Complemento', 'Additional information', 'Información adicional'],
            ['admin_guide_hours', 'Horários', 'Opening hours', 'Horarios'],
            ['admin_guide_hours_hint', 'uma linha por horário', 'one schedule per line', 'un horario por línea'],
            ['admin_guide_create_location', 'Criar unidade', 'Create location', 'Crear sucursal'],
            ['admin_guide_save_location', 'Salvar unidade', 'Save location', 'Guardar sucursal'],
            ['admin_guide_no_phone', 'Sem número', 'No number', 'Sin número'],
            ['admin_guide_custom_link_short', 'Link personalizado', 'Custom link', 'Enlace personalizado'],
            ['admin_guide_description_field', 'Explicação', 'Description', 'Explicación'],
            ['admin_guide_save_sector', 'Salvar setor', 'Save department', 'Guardar sector'],
            ['admin_guide_delete_sector', 'Excluir setor', 'Delete department', 'Eliminar sector'],
            ['admin_guide_delete_location', 'Excluir unidade', 'Delete location', 'Eliminar sucursal'],
            ['admin_guide_confirm_delete_sector', 'Excluir este setor do guia?', 'Delete this department from the guide?', '¿Eliminar este sector de la guía?'],
            ['admin_guide_confirm_delete_location', 'Excluir esta unidade e TODOS os setores vinculados?', 'Delete this location and ALL linked departments?', '¿Eliminar esta sucursal y TODOS los sectores vinculados?'],
            ['whatsapp_admin_title', 'WhatsApp flutuante', 'Floating WhatsApp', 'WhatsApp flotante'],
            ['whatsapp_admin_description', 'Configure o título do atendimento, números, mensagens e as páginas públicas onde cada contato aparece.', 'Configure the service title, numbers, messages and public pages where each contact appears.', 'Configura el título de atención, números, mensajes y páginas públicas donde aparece cada contacto.'],
            ['whatsapp_admin_guide_button', 'Guia de atendimento', 'Service guide', 'Guía de atención'],
            ['whatsapp_admin_edit_icon', 'Editar ícone do WhatsApp', 'Edit WhatsApp icon', 'Editar ícono de WhatsApp'],
            ['whatsapp_admin_info', 'O botão aparece em todas as páginas públicas configuradas e nunca dentro do admin. Use o formato internacional, por exemplo: +55 45 95162-1545.', 'The button appears on configured public pages and never inside admin. Use the international format, for example: +55 45 95162-1545.', 'El botón aparece en las páginas públicas configuradas y nunca dentro del admin. Usa el formato internacional, por ejemplo: +55 45 95162-1545.'],
            ['whatsapp_admin_menu_header', 'Cabeçalho do menu', 'Menu header', 'Encabezado del menú'],
            ['whatsapp_admin_presentation', 'Apresentação do atendimento', 'Service presentation', 'Presentación de atención'],
            ['whatsapp_admin_enabled', 'Ativo', 'Active', 'Activo'],
            ['whatsapp_admin_disabled', 'Desativado', 'Disabled', 'Desactivado'],
            ['whatsapp_admin_main_title', 'Título principal', 'Main title', 'Título principal'],
            ['whatsapp_admin_support_text', 'Texto de apoio', 'Supporting text', 'Texto de apoyo'],
            ['whatsapp_admin_show_site', 'Exibir no site', 'Show on site', 'Mostrar en el sitio'],
            ['whatsapp_admin_save_presentation', 'Salvar apresentação', 'Save presentation', 'Guardar presentación'],
            ['whatsapp_admin_new_destination', 'Novo destino', 'New destination', 'Nuevo destino'],
            ['whatsapp_admin_add_number', 'Adicionar número ao menu', 'Add number to menu', 'Agregar número al menú'],
            ['whatsapp_admin_number_title', 'Título do número', 'Number title', 'Título del número'],
            ['whatsapp_admin_category', 'Categoria / filtro', 'Category / filter', 'Categoría / filtro'],
            ['whatsapp_admin_number_ddi', 'Número com DDI', 'Number with country code', 'Número con código de país'],
            ['whatsapp_admin_order', 'Ordem', 'Order', 'Orden'],
            ['whatsapp_admin_active', 'Ativo', 'Active', 'Activo'],
            ['whatsapp_admin_short_description', 'Explicação discreta na página de contato', 'Short description on the contact page', 'Explicación breve en la página de contacto'],
            ['whatsapp_admin_prefilled_message', 'Mensagem preenchida no WhatsApp', 'WhatsApp prefilled message', 'Mensaje prellenado en WhatsApp'],
            ['whatsapp_admin_contact_icon', 'Ícone na página de contato', 'Contact page icon', 'Ícono en la página de contacto'],
            ['whatsapp_admin_show_contact_page', 'Mostrar na página de contato', 'Show on contact page', 'Mostrar en la página de contacto'],
            ['whatsapp_admin_pages_question', 'Em quais páginas este número aparece?', 'On which pages does this number appear?', '¿En qué páginas aparece este número?'],
            ['whatsapp_admin_add_contact', 'Adicionar contato', 'Add contact', 'Agregar contacto'],
            ['whatsapp_admin_destinations', 'Destinos cadastrados', 'Registered destinations', 'Destinos registrados'],
            ['whatsapp_admin_contacts_count', ':count contato(s)', ':count contact(s)', ':count contacto(s)'],
            ['whatsapp_admin_order_hint', 'A menor ordem aparece primeiro no menu.', 'The lowest order appears first in the menu.', 'El orden menor aparece primero en el menú.'],
            ['whatsapp_admin_test_redirect', 'Testar redirecionamento', 'Test redirect', 'Probar redirección'],
            ['whatsapp_admin_save_contact', 'Salvar contato', 'Save contact', 'Guardar contacto'],
            ['whatsapp_admin_delete_confirm', 'Excluir este contato do WhatsApp?', 'Delete this WhatsApp contact?', '¿Eliminar este contacto de WhatsApp?'],
            ['whatsapp_admin_empty_title', 'Nenhum contato cadastrado', 'No contacts registered', 'No hay contactos registrados'],
            ['whatsapp_admin_empty_text', 'Adicione o primeiro número usando o formulário acima.', 'Add the first number using the form above.', 'Agrega el primer número usando el formulario anterior.'],
            ['whatsapp_admin_success_settings', 'Configuração do WhatsApp atualizada.', 'WhatsApp settings updated.', 'Configuración de WhatsApp actualizada.'],
            ['whatsapp_admin_success_created', 'Contato do WhatsApp adicionado.', 'WhatsApp contact added.', 'Contacto de WhatsApp agregado.'],
            ['whatsapp_admin_success_updated', 'Contato do WhatsApp atualizado.', 'WhatsApp contact updated.', 'Contacto de WhatsApp actualizado.'],
            ['whatsapp_admin_success_deleted', 'Contato do WhatsApp excluído.', 'WhatsApp contact deleted.', 'Contacto de WhatsApp eliminado.'],
            ['whatsapp_admin_invalid_phone', 'Informe o número com DDI e DDD, usando entre 8 e 15 dígitos.', 'Enter the number with country and area codes, using 8 to 15 digits.', 'Ingresa el número con códigos de país y área, usando entre 8 y 15 dígitos.'],
            ['whatsapp_context_all', 'Todas as páginas públicas', 'All public pages', 'Todas las páginas públicas'],
            ['whatsapp_context_home', 'Página inicial', 'Home page', 'Página de inicio'],
            ['whatsapp_context_catalog', 'Categorias, marcas e busca', 'Categories, brands and search', 'Categorías, marcas y búsqueda'],
            ['whatsapp_context_product', 'Página de produto', 'Product page', 'Página de producto'],
            ['whatsapp_context_cart', 'Carrinho', 'Cart', 'Carrito'],
            ['whatsapp_context_checkout', 'Checkout', 'Checkout', 'Checkout'],
            ['whatsapp_context_account', 'Área do cliente', 'Customer area', 'Área del cliente'],
            ['whatsapp_context_blog', 'Blog / SAX News', 'Blog / SAX News', 'Blog / SAX News'],
            ['whatsapp_context_institutional', 'Institucional', 'About us', 'Institucional'],
            ['whatsapp_context_bridal', 'SAX Bridal', 'SAX Bridal', 'SAX Bridal'],
            ['whatsapp_context_palace', 'SAX Palace', 'SAX Palace', 'SAX Palace'],
            ['whatsapp_context_cafe', 'Café & Bistrô', 'Café & Bistro', 'Café y Bistró'],
            ['whatsapp_context_other', 'Outras páginas públicas', 'Other public pages', 'Otras páginas públicas'],
            ['admin_guide_success_location_created', 'Unidade adicionada ao guia.', 'Location added to the guide.', 'Sucursal agregada a la guía.'],
            ['admin_guide_success_location_updated', 'Unidade atualizada.', 'Location updated.', 'Sucursal actualizada.'],
            ['admin_guide_success_location_deleted', 'Unidade e seus setores foram excluídos.', 'Location and its departments were deleted.', 'La sucursal y sus sectores fueron eliminados.'],
            ['admin_guide_success_sector_created', 'Setor adicionado ao guia.', 'Department added to the guide.', 'Sector agregado a la guía.'],
            ['admin_guide_success_sector_updated', 'Setor atualizado.', 'Department updated.', 'Sector actualizado.'],
            ['admin_guide_success_sector_deleted', 'Setor excluído do guia.', 'Department deleted from the guide.', 'Sector eliminado de la guía.'],
        ];

        DB::table('languages')->insertOrIgnore(array_map(static fn (array $row): array => [
            'key' => $row[0], 'pt' => $row[1], 'en' => $row[2], 'es' => $row[3],
            'created_at' => now(), 'updated_at' => now(),
        ], $translations));
    }

    private function translateGuideData(): void
    {
        $locations = [
            'SAX Department Store' => ['Brand, floor and department guide', 'Guía de marcas, pisos y sectores'],
            'Shopping Dubai' => ['SAX Department Store', 'SAX Department Store'],
            'Distrito Perseverancia · Guembe' => ['SAX Department Store', 'SAX Department Store'],
            'Distrito Perseverancia · Casona' => ['Boutiques, luxury, home, eyewear and bridal', 'Boutiques, lujo, hogar, lentes y novias'],
        ];
        foreach ($locations as $name => [$subtitleEn, $subtitleEs]) {
            $query = DB::table('contact_guide_locations')->where('name', $name);
            $row = $query->first();
            if (! $row) continue;
            $hoursEn = str_contains($row->service_hours, 'Segunda')
                ? "Monday to Saturday: 8:30 am to 5:00 pm\nSundays: 9:00 am to 1:00 pm"
                : "Sunday to Thursday: 10:00 am to 9:00 pm\nFridays and Saturdays: 10:00 am to 10:00 pm";
            $hoursEs = str_contains($row->service_hours, 'Segunda')
                ? "Lunes a sábado: 08:30 a 17:00\nDomingos: 09:00 a 13:00"
                : "Domingo a jueves: 10:00 a 21:00\nViernes y sábados: 10:00 a 22:00";
            $query->update([
                'city_en' => $row->city, 'city_es' => $row->city,
                'name_en' => $row->name, 'name_es' => $row->name,
                'subtitle_en' => $subtitleEn, 'subtitle_es' => $subtitleEs,
                'service_hours_en' => $hoursEn, 'service_hours_es' => $hoursEs,
            ]);
        }

        $sectors = [
            'SAX Outlet' => ['SAX Outlet', 'SAX Outlet'], 'Moda feminina' => ['Women’s fashion', 'Moda femenina'],
            'Bolsas e acessórios femininos' => ['Women’s bags and accessories', 'Bolsos y accesorios femeninos'],
            'Moda masculina e casual' => ['Men’s fashion', 'Moda masculina'], 'Sneakers e casual' => ['Sneakers and casual', 'Sneakers y casual'],
            'Perfumeria e édition privée' => ['Perfumery and édition privée', 'Perfumería y édition privée'],
            'Lentes e óculos' => ['Eyewear and optical', 'Lentes y gafas'], 'Home' => ['Home', 'Hogar'],
            'Luxo' => ['Luxury', 'Lujo'], 'Casual e viagem' => ['Casual and travel', 'Casual y viaje'],
            'Kids' => ['Kids', 'Niños'], 'Hugo' => ['Hugo', 'Hugo'], 'Boss' => ['Boss', 'Boss'],
            'Clássicos masculinos' => ['Men’s classics', 'Clásicos masculinos'], 'Novias' => ['Bridal', 'Novias'],
            'Festas' => ['Evening wear', 'Fiestas'], 'Boutiques de luxo' => ['Luxury boutiques', 'Boutiques de lujo'],
            'Moda masculina premium' => ['Premium men’s fashion', 'Moda masculina premium'],
            'Perfumeria' => ['Perfumery', 'Perfumería'], 'Moda casual' => ['Casual fashion', 'Moda casual'],
            'Hugo Boss' => ['Hugo Boss', 'Hugo Boss'], 'Moda e acessórios' => ['Fashion and accessories', 'Moda y accesorios'],
            'Maletas' => ['Luggage', 'Maletas'], 'Novias e festas' => ['Bridal and evening wear', 'Novias y fiestas'],
            'Bebidas e charcutaria' => ['Beverages and delicatessen', 'Bebidas y charcutería'],
            'Atendimento geral' => ['General assistance', 'Atención general'],
            'Bebidas e Home' => ['Beverages and Home', 'Bebidas y Hogar'],
        ];
        $descriptions = [
            'Seleção outlet masculina, feminina e home.' => ['Men’s, women’s and home outlet selection.', 'Selección outlet masculina, femenina y hogar.'],
            'Atendimento do setor masculino.' => ['Men’s department assistance.', 'Atención del sector masculino.'],
            'Perfumeria geral e fragrâncias de nicho.' => ['General perfumery and niche fragrances.', 'Perfumería general y fragancias de nicho.'],
            'Armações, óculos solares, lentes e orientação especializada.' => ['Frames, sunglasses, lenses and specialized guidance.', 'Monturas, gafas de sol, lentes y orientación especializada.'],
            'Atendimento especializado para noivas.' => ['Specialized bridal assistance.', 'Atención especializada para novias.'],
            'Vestidos e produções para festas.' => ['Dresses and styling for special occasions.', 'Vestidos y producciones para fiestas.'],
            'Perfumeria geral e de nicho.' => ['General and niche perfumery.', 'Perfumería general y de nicho.'],
            'Óculos, lentes e atendimento óptico.' => ['Eyewear, lenses and optical assistance.', 'Gafas, lentes y atención óptica.'],
            'Contato em atualização.' => ['Contact being updated.', 'Contacto en actualización.'],
        ];

        DB::table('contact_guide_entries')->orderBy('id')->get()->each(function ($row) use ($sectors, $descriptions): void {
            $floorEn = $row->floor === 'Piso PB' ? 'Ground Floor' : preg_replace('/^Piso /', 'Floor ', $row->floor);
            [$sectorEn, $sectorEs] = $sectors[$row->sector] ?? [$row->sector, $row->sector];
            [$descriptionEn, $descriptionEs] = $descriptions[$row->description] ?? [$row->description, $row->description];
            DB::table('contact_guide_entries')->where('id', $row->id)->update([
                'floor_en' => $floorEn, 'floor_es' => $row->floor,
                'sector_en' => $sectorEn, 'sector_es' => $sectorEs,
                'description_en' => $descriptionEn, 'description_es' => $descriptionEs,
            ]);
        });
    }

    public function down(): void
    {
        DB::table('languages')->whereIn('key', self::KEYS)->delete();
        Schema::table('contact_guide_entries', function (Blueprint $table) {
            $table->dropColumn(['floor_en', 'floor_es', 'sector_en', 'sector_es', 'description_en', 'description_es']);
        });
        Schema::table('contact_guide_locations', function (Blueprint $table) {
            $table->dropColumn(['city_en', 'city_es', 'name_en', 'name_es', 'subtitle_en', 'subtitle_es', 'service_hours_en', 'service_hours_es']);
        });
    }
};
