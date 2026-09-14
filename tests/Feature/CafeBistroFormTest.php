<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\CafeBistroAdminController;
use App\Models\CafeBistro;
use App\Models\PageTranslation;
use App\Services\ImageConverterService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CafeBistroFormTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // These tables exist only in the PHPUnit in-memory SQLite database.
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        Schema::create('cafe_bistros', function (Blueprint $table) {
            $table->id();
            foreach ((new CafeBistro)->getFillable() as $field) $table->text($field)->nullable();
            $table->timestamps();
        });
        Schema::create('page_translations', function (Blueprint $table) {
            $table->id();
            foreach ((new PageTranslation)->getFillable() as $field) $table->text($field)->nullable();
            $table->timestamps();
        });
        $this->app->useStoragePath(sys_get_temp_dir().'/sax-cafe-form-tests-'.getmypid());
        Storage::fake('public');
    }

    public function test_all_sections_save_and_gallery_removals_persist(): void
    {
        $cafe = CafeBistro::create(['name'=>'QA','slug'=>'qa','cardapio_galeria'=>['keep.webp','remove.webp'], 'eventos_galeria'=>['event.webp']]);
        $payload = ['telefono'=>'123', 'whatsapp'=>'456', 'instagram_url'=>'https://instagram.com/qa', 'facebook_url'=>'https://facebook.com/qa', 'mapa_embed'=>'<iframe></iframe>', 'eventos_tipos'=>['Bodas','<Evento>'], 'cardapio_galeria_actual'=>['keep.webp']];
        foreach (['segunda','terca_quinta','sexta_sabado','domingo'] as $day) $payload['horario_'.$day]='09:00 — 23:00';
        $fields = ['hero_titulo','hero_subtitulo','sobre_titulo','sobre_texto','cardapio_titulo','cardapio_subtitulo','eventos_titulo','eventos_subtitulo','eventos_texto','direccion'];
        foreach (['pt-br','es','en'] as $locale) foreach ($fields as $field) $payload['translate'][$locale]['cafe_'.$field]="$locale $field";
        $this->mock(ImageConverterService::class, function ($mock) {
            $mock->shouldReceive('toWebp')->times(4)->andReturn('hero.webp','about.webp','event-new.webp','menu-new.webp');
        });
        $request = Request::create('/admin/cafe_bistro/'.$cafe->id, 'POST', $payload, [], [
            'hero_imagen'=>UploadedFile::fake()->image('hero.png'),
            'sobre_imagen'=>UploadedFile::fake()->image('about.png'),
            'eventos_galeria'=>[UploadedFile::fake()->image('event.png')],
            'cardapio_galeria'=>[UploadedFile::fake()->image('menu.png')],
            'cardapio_pdf'=>UploadedFile::fake()->createWithContent('menu.pdf', "%PDF-1.4\n1 0 obj\n<<>>\nendobj\n%%EOF"),
        ]);
        $request->setLaravelSession(app('session.store'));
        $response = app(CafeBistroAdminController::class)->update($request, $cafe->id);
        $this->assertSame(302, $response->getStatusCode());
        $cafe->refresh();
        foreach (['telefono','whatsapp','instagram_url','facebook_url','mapa_embed','eventos_tipos'] as $field) $this->assertSame($payload[$field], $cafe->$field);
        $this->assertSame(['keep.webp','menu-new.webp'], $cafe->cardapio_galeria);
        $this->assertSame(['event-new.webp'], $cafe->eventos_galeria);
        $this->assertSame('hero.webp', $cafe->hero_imagen);
        $this->assertSame('about.webp', $cafe->sobre_imagen);
        Storage::disk('public')->assertExists($cafe->cardapio_pdf);
        foreach ($cafe->horarios as $value) $this->assertSame('09:00 — 23:00', $value);
        foreach ($cafe->translations as $translation) foreach ($fields as $field) $this->assertSame($translation->locale.' '.$field, $translation->{'cafe_'.$field});
        $this->assertCount(3, $cafe->translations);
    }

    public function test_combined_menu_limit_rejects_before_storing_uploads(): void
    {
        $existing = array_map(fn ($i) => "image$i.webp", range(1,8));
        $cafe = CafeBistro::create(['cardapio_galeria'=>$existing]);
        $request = Request::create('/', 'POST', ['cardapio_galeria_actual'=>$existing], [], ['cardapio_galeria'=>[UploadedFile::fake()->image('extra.png')]]);
        $this->expectException(ValidationException::class);
        app(CafeBistroAdminController::class)->update($request, $cafe->id);
    }
}
