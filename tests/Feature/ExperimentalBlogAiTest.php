<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Services\BlogAiService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Mockery;
use Tests\TestCase;

class ExperimentalBlogAiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::dropIfExists('blogs');
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('subtitle')->nullable();
            $table->integer('read_time')->nullable();
            $table->string('slug')->unique();
            $table->string('image')->nullable();
            $table->string('image_caption')->nullable();
            $table->json('gallery')->nullable();
            $table->text('content');
            $table->text('meta_description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->string('author')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->timestamps();
        });
        $this->withoutMiddleware();
    }

    public function test_experiment_is_hidden_when_flag_is_disabled(): void
    {
        $this->app->detectEnvironment(fn () => 'staging');
        config(['services.openai.blog_experiment_enabled' => false]);

        $this->postJson(route('admin.blogs.ai-experiment.generate'), ['topic' => 'Tema'])
            ->assertNotFound();
    }

    public function test_experiment_is_unavailable_in_production_even_with_flag_enabled(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        config(['services.openai.blog_experiment_enabled' => true]);

        $this->postJson(route('admin.blogs.ai-experiment.generate'), ['topic' => 'Tema'])
            ->assertNotFound();
    }

    public function test_admin_can_generate_form_fields_without_saving_an_article_in_staging(): void
    {
        $this->app->detectEnvironment(fn () => 'staging');
        config(['services.openai.blog_experiment_enabled' => true]);
        $service = Mockery::mock(BlogAiService::class);
        $service->shouldReceive('generate')->once()->andReturn([
            'title' => 'Dior Sauvage Eau de Parfum: carácter y composición',
            'subtitle' => 'Una lectura editorial de la fragancia.',
            'meta_description' => 'Notas, carácter y ocasiones de uso de Dior Sauvage Eau de Parfum.',
            'content_html' => '<p>Contenido seguro.</p>',
            'read_time' => 5,
            'author' => 'SAX',
            'sources' => [['title' => 'Dior', 'url' => 'https://www.dior.com/', 'reason' => 'Fuente oficial.']],
            'image_suggestions' => [['description' => 'Frasco oficial', 'search_query' => 'Dior Sauvage EDP official', 'source_url' => 'https://www.dior.com/']],
        ]);
        $service->shouldReceive('contentWithSources')->once()->andReturn('<p>Contenido seguro.</p><h2>Fuentes consultadas</h2>');
        $this->app->instance(BlogAiService::class, $service);

        $response = $this->postJson(route('admin.blogs.ai-experiment.generate'), [
                'topic' => 'Dior Sauvage Eau de Parfum',
                'focus' => 'Dior Sauvage',
                'instructions' => 'Hablar de notas y ocasiones de uso.',
        ]);

        $response->assertOk()
            ->assertJsonPath('blog.title', 'Dior Sauvage Eau de Parfum: carácter y composición')
            ->assertJsonPath('blog.subtitle', 'Una lectura editorial de la fragancia.')
            ->assertJsonPath('blog.author', 'SAX')
            ->assertJsonPath('blog.content', '<p>Contenido seguro.</p><h2>Fuentes consultadas</h2>')
            ->assertJsonMissingPath('blog.public_url')
            ->assertJsonMissingPath('blog.edit_url');
        $this->assertSame(0, Blog::count());
    }
}
