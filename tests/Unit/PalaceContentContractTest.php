<?php

namespace Tests\Unit;

use Tests\TestCase;

class PalaceContentContractTest extends TestCase
{
    private const TRANSLATABLE_FIELDS = [
        'palace_hero_titulo',
        'palace_hero_descricao',
        'palace_bar_titulo',
        'palace_bar_descricao',
        'palace_eventos_titulo',
        'palace_eventos_descricao',
        'palace_tematica_tag',
        'palace_tematica_titulo',
        'palace_tematica_descricao',
        'palace_tematica_preco',
        'palace_gastronomia_titulo',
        'palace_gastronomia_cafe_desc',
        'palace_gastronomia_almoco_desc',
        'palace_gastronomia_jantar_desc',
        'palace_contato_endereco',
        'palace_contato_horario_segunda',
        'palace_contato_horario_sabado',
        'palace_contato_horario_domingo',
    ];

    public function test_every_palace_text_field_is_editable_and_validated(): void
    {
        $form = file_get_contents(resource_path('views/admin/palace/edit.blade.php'));
        $controller = file_get_contents(app_path('Http/Controllers/Admin/PalaceAdminController.php'));

        foreach (self::TRANSLATABLE_FIELDS as $field) {
            $this->assertStringContainsString($field, $form, "Campo ausente no formulário: {$field}");
            $this->assertStringContainsString("translate.*.{$field}", $controller, "Validação ausente: {$field}");
            $this->assertStringContainsString("'{$field}'", $controller, "Persistência ausente: {$field}");
        }

        $this->assertStringNotContainsString('gastronomia_descricao', $form);
    }

    public function test_public_page_consumes_translated_palace_content(): void
    {
        $views = collect([
            'components/experiencia.blade.php',
            'components/sobre.blade.php',
            'components/sections.blade.php',
            'components/galeria.blade.php',
            'tematica.blade.php',
            'location.blade.php',
            'footer.blade.php',
        ])->map(fn (string $file) => file_get_contents(resource_path('views/palace/'.$file)))->implode("\n");

        foreach (self::TRANSLATABLE_FIELDS as $field) {
            $this->assertStringContainsString('$t->'.$field, $views, "Campo não utilizado no front: {$field}");
        }
    }
}
