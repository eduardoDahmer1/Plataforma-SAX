<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\StorefrontFaviconController;
use App\Models\StorefrontFavicon;
use App\Services\FaviconImageService;
use App\Services\StorefrontFaviconService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StorefrontFaviconUploadTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        Schema::create('storefront_favicons', function (Blueprint $table): void {
            $table->id();
            $table->string('layout')->unique();
            $table->string('path');
            $table->timestamps();
        });
        Storage::fake('public');
    }

    public function test_ajax_upload_and_reset_keep_sax_and_optical_icons_separate(): void
    {
        $controller = app(StorefrontFaviconController::class);
        $favicons = app(StorefrontFaviconService::class);
        $images = app(FaviconImageService::class);

        foreach (['sax' => 'png', 'vista' => 'jpg'] as $layout => $extension) {
            $request = Request::create('/admin/identidade-visual/favicon/'.$layout, 'POST', [], [], [
                'favicon' => UploadedFile::fake()->image('favicon.'.$extension, 300, 100),
            ], ['HTTP_ACCEPT' => 'application/json']);

            $response = $controller->update($request, $layout, $favicons, $images);
            $this->assertSame(200, $response->getStatusCode());
            $this->assertTrue($response->getData(true)['success']);
            $record = StorefrontFavicon::query()->where('layout', $layout)->firstOrFail();
            Storage::disk('public')->assertExists($record->path);
            $this->assertStringEndsWith('.png', $record->path);
        }

        $saxPath = StorefrontFavicon::query()->where('layout', 'sax')->value('path');
        $vistaPath = StorefrontFavicon::query()->where('layout', 'vista')->value('path');
        $this->assertNotSame($saxPath, $vistaPath);

        $reset = Request::create('/admin/identidade-visual/favicon/sax', 'DELETE', [], [], [], [
            'HTTP_ACCEPT' => 'application/json',
        ]);
        $response = $controller->destroy($reset, 'sax', $favicons);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringEndsWith('/images/favicons/sax.svg', $response->getData(true)['url']);
        Storage::disk('public')->assertMissing($saxPath);
        Storage::disk('public')->assertExists($vistaPath);
    }
}
