<?php

namespace App\Console\Commands;

use App\Models\CatalogAttribute;
use App\Services\CatalogAttributeService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SeedOpticalAttributes extends Command
{
    protected $signature = 'catalog:seed-optical-attributes';

    protected $description = 'Crea un diccionario óptico inicial sin asignar características a productos ni alterar categorías ERP.';

    public function handle(CatalogAttributeService $catalog): int
    {
        if (! $catalog->available()) {
            $this->error('Aplicá primero la migración de atributos.');

            return self::FAILURE;
        }
        // Labels and choices define vocabulary, never facts about a particular product.
        $definitions = [
            ['Forma de montura', 'Formato da armação', 'Frame shape', 'single', null, true, [
                ['Ovalada', 'Oval', 'Oval'], ['Redonda', 'Redonda', 'Round'], ['Rectangular', 'Retangular', 'Rectangular'], ['Aviador', 'Aviador', 'Aviator'], ['Ojo de gato', 'Gatinho', 'Cat-eye']]],
            ['Material de montura', 'Material da armação', 'Frame material', 'multiple', null, true, [['Acetato', 'Acetato', 'Acetate'], ['Metal', 'Metal', 'Metal'], ['Titanio', 'Titânio', 'Titanium'], ['Plástico', 'Plástico', 'Plastic']]],
            ['Color de montura', 'Cor da armação', 'Frame color', 'multiple', null, false, [['Negro', 'Preto', 'Black'], ['Marrón', 'Marrom', 'Brown'], ['Dorado', 'Dourado', 'Gold'], ['Plateado', 'Prata', 'Silver'], ['Transparente', 'Transparente', 'Clear']]],
            ['Color de lente', 'Cor da lente', 'Lens color', 'multiple', null, false, [['Gris', 'Cinza', 'Gray'], ['Marrón', 'Marrom', 'Brown'], ['Verde', 'Verde', 'Green'], ['Azul', 'Azul', 'Blue']]],
            ['Polarización', 'Polarização', 'Polarization', 'boolean', null, false, []],
            ['Ancho de lente', 'Largura da lente', 'Lens width', 'number', 'mm', false, []],
            ['Ancho de puente', 'Largura da ponte', 'Bridge width', 'number', 'mm', false, []],
            ['Longitud de patilla', 'Comprimento da haste', 'Temple length', 'number', 'mm', false, []],
            ['Potencia esférica', 'Potência esférica', 'Sphere power', 'number', 'D', false, []],
            ['Cilindro', 'Cilindro', 'Cylinder', 'number', 'D', false, []],
            ['Eje', 'Eixo', 'Axis', 'number', '°', false, []],
            ['Curva base', 'Curva base', 'Base curve', 'number', 'mm', false, []],
            ['Diámetro', 'Diâmetro', 'Diameter', 'number', 'mm', false, []],
            ['Material de lente de contacto', 'Material da lente de contato', 'Contact lens material', 'single', null, false, [['Hidrogel', 'Hidrogel', 'Hydrogel'], ['Hidrogel de silicona', 'Hidrogel de silicone', 'Silicone hydrogel']]],
            ['Reemplazo de lente de contacto', 'Troca da lente de contato', 'Contact lens replacement', 'single', null, false, [['Diario', 'Diário', 'Daily'], ['Quincenal', 'Quinzenal', 'Two-week'], ['Mensual', 'Mensal', 'Monthly']]],
        ];
        foreach ($definitions as [$es,$pt,$en,$type,$unit,$inherit,$options]) {
            if (CatalogAttribute::where('code', Str::slug($es, '_'))->exists()) {
                continue;
            } // Preserve administrative edits on reruns.
            $attribute = $catalog->saveDefinition(['translate' => $this->labels($es, $pt, $en), 'data_type' => $type, 'unit' => $unit, 'is_active' => true, 'inherit_from_parent' => $inherit]);
            foreach ($options as [$oe,$op,$on]) {
                $catalog->saveOption($attribute, ['translate' => $this->labels($oe, $op, $on), 'is_active' => true]);
            }
        }
        $this->info('Diccionario óptico creado. No se asignaron valores a productos.');

        return self::SUCCESS;
    }

    private function labels(string $es, string $pt, string $en): array
    {
        return ['es' => ['name' => $es], 'pt-br' => ['name' => $pt], 'en' => ['name' => $en]];
    }
}
