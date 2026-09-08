<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_guide_locations', function (Blueprint $table) {
            $table->id();
            $table->string('city', 100);
            $table->string('name', 140);
            $table->string('subtitle', 180)->nullable();
            $table->text('service_hours')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->index(['active', 'sort_order'], 'contact_guide_locations_visibility_index');
        });

        Schema::create('contact_guide_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained('contact_guide_locations')->cascadeOnDelete();
            $table->string('floor', 80);
            $table->string('sector', 160);
            $table->string('description', 500)->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('whatsapp_url', 500)->nullable();
            $table->json('brands')->nullable();
            $table->boolean('is_optical')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->index(['active', 'is_optical', 'sort_order'], 'contact_guide_entries_visibility_index');
        });

        $now = now();
        $location = static function (
            string $city,
            string $name,
            string $subtitle,
            string $hours,
            int $order
        ) use ($now): int {
            return (int) DB::table('contact_guide_locations')->insertGetId([
                'city' => $city,
                'name' => $name,
                'subtitle' => $subtitle,
                'service_hours' => $hours,
                'sort_order' => $order,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        };
        $entry = static function (
            int $locationId,
            string $floor,
            string $sector,
            ?string $phone,
            array $brands,
            int $order,
            bool $isOptical = false,
            ?string $description = null
        ) use ($now): void {
            DB::table('contact_guide_entries')->insert([
                'location_id' => $locationId,
                'floor' => $floor,
                'sector' => $sector,
                'description' => $description,
                'phone' => $phone,
                'whatsapp_url' => null,
                'brands' => $brands ? json_encode($brands, JSON_UNESCAPED_UNICODE) : null,
                'is_optical' => $isOptical,
                'sort_order' => $order,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        };

        $cde = $location(
            'Ciudad del Este',
            'SAX Department Store',
            'Guia de marcas, pisos e setores',
            "Segunda a sábado: 08h30 às 17h00\nDomingos: 09h00 às 13h00",
            10
        );
        $pjc = $location(
            'Pedro Juan Caballero',
            'Shopping Dubai',
            'SAX Department Store',
            "Domingo a quinta: 10h00 às 21h00\nSextas e sábados: 10h00 às 22h00",
            20
        );
        $guembe = $location(
            'Asunción',
            'Distrito Perseverancia · Guembe',
            'SAX Department Store',
            "Domingo a quinta: 10h00 às 21h00\nSextas e sábados: 10h00 às 22h00",
            30
        );
        $casona = $location(
            'Asunción',
            'Distrito Perseverancia · Casona',
            'Boutiques, luxo, home, lentes e bridal',
            "Domingo a quinta: 10h00 às 21h00\nSextas e sábados: 10h00 às 22h00",
            40
        );

        $entry($cde, 'Piso 2', 'SAX Outlet', '+595 994 369599', [
            'Boss', 'Hugo', 'Diesel', 'Versace', 'Salvatore Ferragamo', 'Ralph Lauren', 'Setor Home',
            'Karl Lagerfeld', 'Armani', 'Emporio Armani', 'Boggi', 'Kenzo', 'Camicissima', 'DV Collection',
            'Paul & Shark', 'Dsquared2', 'Dolce & Gabbana', 'Valentino', 'Zegna',
        ], 10, false, 'Seleção outlet masculina, feminina e home.');
        $entry($cde, 'Piso 3', 'Moda feminina', '+595 984 163267', [
            'Hugo Feminino', 'Boss Feminino', 'En Saison', 'Twinset', 'Eliatt', 'Curaae', 'Sleen', 'BCBG',
            'Kenzo', 'Karl Lagerfeld Feminino',
        ], 20);
        $entry($cde, 'Piso 3', 'Bolsas e acessórios femininos', '+595 984 163267', [
            'DV Collection', 'Zadig & Voltaire', 'JW PEI', 'Boss Feminino Carteras',
        ], 21);
        $entry($cde, 'Piso 3', 'Moda masculina e casual', '+595 981 710103', [
            'Emporio Armani', 'Karl Lagerfeld Masculino',
        ], 22, false, 'Atendimento do setor masculino.');
        $entry($cde, 'Piso 3', 'Sneakers e casual', '+595 981 479635', ['On', 'Veja'], 23);
        $entry($cde, 'Piso 3', 'Perfumeria e édition privée', '+595 994 196820', [], 24, false, 'Perfumeria geral e fragrâncias de nicho.');
        $entry($cde, 'Piso 4', 'Lentes e óculos', '+595 984 103003', [
            'Hugo Boss', 'Emporio Armani', 'Versace', 'Kenzo', 'Ralph Lauren', 'Fendi', 'Guess', 'Ferrari',
            'Pierre Cardin', 'Carolina Herrera', 'Tommy', 'David Beckham', 'Michael Kors', 'Tom Ford',
            'Moschino', 'Timberland', 'Adidas', 'Persol', 'Montblanc', 'Miraflex', 'Off-White', 'Palm Angels',
            'Porsche', 'Balmain', 'Zegna', 'Fred', 'Dolce & Gabbana', 'Burberry', 'Valentino', 'YSL',
            'Bottega Veneta', 'Gucci', 'Prada', 'Loewe', 'Dior', 'Bvlgari', 'TAG Heuer', 'Celine',
            'Stella McCartney', 'Givenchy', 'Cartier', 'Felini', 'Ray-Ban', 'Oakley', 'Swarovski',
            'Tiffany & Co.', 'Miu Miu', 'Chloé',
        ], 30, true, 'Armações, óculos solares, lentes e orientação especializada.');
        $entry($cde, 'Piso 5', 'Home', '+595 984 107356', [
            'Fendi', 'Versace', 'Ralph Lauren', 'Voluspa', 'St. Louis', 'DV Collection', 'Armani', 'Baccarat',
            'Christofle', 'Roberto Cavalli', 'Lalique', 'Daum', 'Strauss', 'Bernardaud',
        ], 40);
        $entry($cde, 'Piso 6', 'Luxo', '+595 986 246763', [
            'Valentino', 'Ferragamo', 'Versace', 'Yves Saint Laurent', 'Gianvito Rossi', 'Burberry',
            'Dolce & Gabbana', 'Fendi', 'Bottega Veneta', 'Zegna', 'Golden Goose', 'Jacquemus',
            'Christian Dior', 'Borsalino', 'Celine', 'Gucci', 'Loewe', 'Vilebrequin', 'Philipp Plein',
        ], 50);
        $entry($cde, 'Piso 7', 'Casual e viagem', '+595 984 102968', [
            'Diesel', 'Emporio Armani 7', 'Samsonite', 'American Tourister', 'Armani Exchange',
        ], 60);
        $entry($cde, 'Piso 7', 'Kids', '+595 993 281123', [
            'Stokke Kids', 'Yoyo Kids', 'Hugo Kids', 'Boss Kids', 'Kenzo Kids', 'Ralph Lauren Kids',
            'Karl Lagerfeld Kids',
        ], 61);
        $entry($cde, 'Piso 8', 'Hugo', '+595 993 277710', ['Hugo'], 70);
        $entry($cde, 'Piso 8', 'Boss', '+595 994 360457', ['Boss'], 71);
        $entry($cde, 'Piso 8', 'Clássicos masculinos', '+595 994 512426', ['Ralph Lauren', 'Boggi Milano', 'Paul & Shark'], 72);
        $entry($cde, 'Piso 9', 'Novias', '+595 981 527848', [
            'Pronovias', 'Pronovias Privée', 'Atelier Pronovias', 'Nicole Milano', 'White One', 'St. Patrick',
            'Lady Bird', 'Rosa Clará', 'Allure Bridals', 'Abella', 'Madison James',
        ], 80, false, 'Atendimento especializado para noivas.');
        $entry($cde, 'Piso 9', 'Festas', '+595 993 287342', [
            'Theia', 'Jovani', 'Tadashi Shoji', 'Bronx & Banco', 'Aidan Mattox', 'Eliatt', 'Kay Unger',
            'Ladivine', 'Tarik Ediz', 'Vera Wang', 'Marfil', 'Marchesa Notte', 'Amur',
        ], 81, false, 'Vestidos e produções para festas.');

        $entry($pjc, 'Piso PB', 'Boutiques de luxo', '+595 993 011507', ['Valentino', 'Versace', 'Bottega Veneta'], 10);
        $entry($pjc, 'Piso PB', 'Moda masculina premium', '+595 993 011512', ['Emporio Armani', 'Ferragamo', 'Zegna', 'Paul & Shark', 'Borsalino'], 11);
        $entry($pjc, 'Piso PB', 'Perfumeria', '+595 993 011501', ['Dolce & Gabbana', 'Burberry', 'Fendi', 'Gucci', 'Yves Saint Laurent', 'Prada'], 12, false, 'Perfumeria geral e de nicho.');
        $entry($pjc, 'Piso 1', 'Sneakers e casual', '+595 993 011504', ['On', 'Veja', 'Golden Goose'], 20);
        $entry($pjc, 'Piso 1', 'Moda casual', '+595 993 011508', ['Diesel', 'Polo Ralph Lauren', 'Boggi Milano'], 21);
        $entry($pjc, 'Piso 1', 'Hugo Boss', '+595 993 011517', ['Hugo', 'Boss'], 22);
        $entry($pjc, 'Piso 1', 'Moda e acessórios', '+595 993 011511', ['DV Collection', 'Karl Lagerfeld', 'Zadig & Voltaire'], 23);
        $entry($pjc, 'Piso 1', 'Kids', '+595 993 011506', ['Ralph Lauren Kids', 'Boss Kids'], 24);
        $entry($pjc, 'Piso 2', 'Lentes e óculos', '+595 993 011514', [], 30, true, 'Óculos, lentes e atendimento óptico.');
        $entry($pjc, 'Piso 2', 'Maletas', '+595 993 011514', ['Samsonite', 'American Tourister'], 31);
        $entry($pjc, 'Piso 2', 'Novias e festas', '+595 993 011505', [], 32);
        $entry($pjc, 'Piso 2', 'Bebidas e charcutaria', '+595 993 011510', [], 33);

        $entry($guembe, 'Piso PB', 'Atendimento geral', null, [], 10, false, 'Contato em atualização.');
        $entry($guembe, 'Piso 1', 'Casual e viagem', '+595 986 247877', ['Diesel', 'Boggi', 'Ralph Lauren', 'Maletas'], 20);
        $entry($guembe, 'Piso 1', 'Hugo Boss', '+595 986 135883', ['Hugo', 'Boss'], 21);

        $entry($casona, 'Piso PB', 'Boutiques de luxo', '+595 985 758117', ['Valentino', 'Ferragamo', 'Versace', 'Zadig & Voltaire', 'Bottega Veneta'], 10);
        $entry($casona, 'Piso PB', 'Moda masculina premium', '+595 981 113834', ['Zegna', 'Paul & Shark', 'Vilebrequin', 'Emporio Armani', 'Borsalino'], 11);
        $entry($casona, 'Piso 1', 'Bebidas e Home', '+595 981 410968', [], 20);
        $entry($casona, 'Piso 1', 'Lentes e óculos', '+595 983 114651', [], 21, true, 'Óculos, lentes e atendimento óptico.');
        $entry($casona, 'Piso 1', 'Novias e festas', '+595 986 130390', [], 22);
        $entry($casona, 'Piso 1', 'Luxo', '+595 985 758117', [
            'Burberry', 'Jacquemus', 'Gianvito Rossi', 'Gucci', 'Yves Saint Laurent', 'Fendi',
            'Dolce & Gabbana', 'Celine', 'Dior',
        ], 23);
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_guide_entries');
        Schema::dropIfExists('contact_guide_locations');
    }
};
