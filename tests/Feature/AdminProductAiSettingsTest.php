<?php

namespace Tests\Feature;

use App\Jobs\GenerateProductAiPreparation;
use App\Models\Product;
use App\Models\ProductAiBatch;
use App\Models\ProductAiBatchItem;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\OpenAICatalogService;
use App\Services\ProductAiProposalApplier;
use App\Services\ProductAiSettingsService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\Support\UsesProductAiDatabase;
use Tests\TestCase;

class AdminProductAiSettingsTest extends TestCase
{
    use UsesProductAiDatabase;

    public function test_only_master_admin_can_change_ai_settings(): void
    {
        foreach ([User::TYPE_CUSTOMER, User::TYPE_ADMIN_EDITOR] as $type) {
            $this->actingAs(User::factory()->create(['user_type' => $type]))
                ->putJson(route('admin.ai-settings.update'), ['ai_enabled' => false, 'ai_key_source' => 'environment'])
                ->assertForbidden();
        }
        $this->assertTrue(SystemSetting::first()->ai_enabled);
    }

    public function test_key_is_encrypted_hidden_preserved_and_removable(): void
    {
        $this->actingAs(User::factory()->create(['user_type' => User::TYPE_ADMIN_MASTER]));
        $url = route('admin.ai-settings.update');
        $this->putJson($url, ['ai_enabled' => true, 'ai_key_source' => 'stored', 'ai_api_key' => 'secret-test-key'])->assertRedirect();
        $settings = SystemSetting::first();
        $this->assertSame('secret-test-key', $settings->ai_api_key);
        $this->assertNotSame('secret-test-key', $settings->getRawOriginal('ai_api_key'));
        $this->assertArrayNotHasKey('ai_api_key', $settings->toArray());
        $this->putJson($url, ['ai_enabled' => false, 'ai_key_source' => 'stored', 'ai_api_key' => ''])->assertRedirect();
        $this->assertSame('secret-test-key', SystemSetting::first()->ai_api_key);
        $this->putJson($url, ['ai_enabled' => false, 'ai_key_source' => 'stored', 'remove_api_key' => true])->assertRedirect();
        $this->assertNull(SystemSetting::first()->ai_api_key);
    }

    public function test_invalid_submission_never_flashes_key_to_session(): void
    {
        $this->actingAs(User::factory()->create(['user_type' => User::TYPE_ADMIN_MASTER]))
            ->from(route('admin.ai-settings.edit'))
            ->put(route('admin.ai-settings.update'), ['ai_enabled' => true, 'ai_key_source' => 'invalid', 'ai_api_key' => 'secret-test-key'])
            ->assertSessionHasErrors('ai_key_source')
            ->assertSessionMissing('_old_input.ai_api_key');
    }

    public function test_key_source_is_explicit_and_changes_are_read_without_restarting_service(): void
    {
        config(['services.openai.api_key' => 'environment-key']);
        $service = app(ProductAiSettingsService::class);
        $this->assertSame('environment-key', $service->apiKey());
        SystemSetting::first()->update(['ai_key_source' => 'stored', 'ai_api_key' => 'stored-key']);
        $this->assertSame('stored-key', $service->apiKey());
        SystemSetting::first()->update(['ai_api_key' => null]);
        $this->assertSame('', $service->apiKey());
        $this->assertNotNull($service->unavailableReason());
    }

    public function test_enabling_requires_a_key_from_the_selected_source(): void
    {
        config(['services.openai.api_key' => 'environment-key']);
        $this->actingAs(User::factory()->create(['user_type' => User::TYPE_ADMIN_MASTER]))
            ->putJson(route('admin.ai-settings.update'), ['ai_enabled' => true, 'ai_key_source' => 'stored'])
            ->assertUnprocessable()->assertJsonValidationErrors('ai_key_source');
    }

    public function test_disabled_ai_blocks_individual_and_batch_generation_without_changing_preparations(): void
    {
        Http::fake();
        Queue::fake();
        SystemSetting::first()->update(['ai_enabled' => false]);
        $product = Product::create(['sku' => 'AI-TEST', 'price' => 10]);
        $batch = ProductAiBatch::create(['source_type' => 'paste', 'status' => 'draft']);
        $this->actingAs(User::factory()->create(['user_type' => User::TYPE_ADMIN_MASTER]));
        $this->postJson(route('admin.products.completeWithAi', $product))->assertUnprocessable()->assertJsonValidationErrors('ai');
        $this->postJson(route('admin.products.ai-batches.dispatch', $batch))->assertUnprocessable()->assertJsonValidationErrors('ai');
        $this->assertDatabaseCount('product_ai_preparations', 0);
        $this->assertSame('draft', $batch->fresh()->status);
        Http::assertNothingSent();
        Queue::assertNothingPushed();
    }

    public function test_disabled_ai_is_also_blocked_inside_the_provider_service(): void
    {
        Http::fake();
        SystemSetting::first()->update(['ai_enabled' => false]);
        try {
            app(OpenAICatalogService::class)->generateProductProposal(new Product);
            $this->fail('Disabled AI must not reach the provider.');
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $this->assertArrayHasKey('ai', $exception->errors());
        }
        Http::assertNothingSent();
    }

    public function test_queued_jobs_pause_without_failing_or_consuming_api_calls(): void
    {
        SystemSetting::first()->update(['ai_enabled' => false]);
        $product = Product::create(['sku' => 'AI-QUEUED', 'price' => 10]);
        $batch = ProductAiBatch::create(['source_type' => 'paste', 'status' => 'queued']);
        $item = ProductAiBatchItem::create(['batch_id' => $batch->id, 'product_id' => $product->id, 'status' => 'queued']);
        $job = new GenerateProductAiPreparation($item->id, $product->id);
        $queueJob = \Mockery::mock(\Illuminate\Contracts\Queue\Job::class);
        $queueJob->shouldReceive('release')->once()->with(60);
        $job->setJob($queueJob);
        $provider = \Mockery::mock(OpenAICatalogService::class);
        $provider->shouldNotReceive('generateProductProposal');
        $job->handle($provider, app(ProductAiProposalApplier::class));
        $this->assertSame('queued', $item->fresh()->status);
        $this->assertDatabaseCount('product_ai_preparations', 0);
        $this->assertSame(0, $job->tries);
        $this->assertSame(3, $job->maxExceptions);

        SystemSetting::first()->update(['ai_enabled' => true]);
        config(['services.openai.api_key' => 'test-key']);
        $resumedProvider = \Mockery::mock(OpenAICatalogService::class);
        $resumedProvider->shouldReceive('generateProductProposal')->once()->andReturn([
            'research' => ['status' => 'not_found'],
        ]);
        $job->handle($resumedProvider, app(ProductAiProposalApplier::class));
        $this->assertSame('not_found', $item->fresh()->status);
    }
}
