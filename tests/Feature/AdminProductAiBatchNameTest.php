<?php

namespace Tests\Feature;

use App\Models\ProductAiBatch;
use App\Models\User;
use Tests\Support\UsesProductAiDatabase;
use Tests\TestCase;

class AdminProductAiBatchNameTest extends TestCase
{
    use UsesProductAiDatabase;

    public function test_admin_can_name_any_batch_without_changing_its_processing_data(): void
    {
        $admin = User::factory()->create(['user_type' => User::TYPE_ADMIN_MASTER]);
        foreach (['draft', 'queued', 'processing', 'completed'] as $status) {
            $batch = ProductAiBatch::create([
                'source_type' => 'paste', 'status' => $status,
                'original_filename' => 'original.xlsx', 'input_count' => 5, 'eligible_count' => 3,
            ]);
            $this->actingAs($admin)->patchJson(route('admin.products.ai-batches.rename', $batch), [
                'name' => '  Bolsas pendientes de fotos  ', 'status' => 'changed', 'input_count' => 999,
            ])->assertOk()->assertJsonPath('name', 'Bolsas pendientes de fotos')
                ->assertJsonPath('display_name', 'Bolsas pendientes de fotos');
            $batch->refresh();
            $this->assertSame('Bolsas pendientes de fotos', $batch->name);
            $this->assertSame($status, $batch->status);
            $this->assertSame(5, $batch->input_count);
            $this->assertSame(3, $batch->eligible_count);
            $this->assertSame('original.xlsx', $batch->original_filename);
        }
    }

    public function test_empty_name_restores_the_default_and_existing_batches_have_a_default(): void
    {
        $admin = User::factory()->create(['user_type' => User::TYPE_ADMIN_MASTER]);
        $batch = ProductAiBatch::create(['source_type' => 'paste', 'status' => 'draft']);
        $this->assertSame('Lote de IA #'.$batch->id, $batch->display_name);
        $batch->update(['name' => 'Nombre anterior']);
        foreach (['   ', null] as $name) {
            $this->actingAs($admin)->patchJson(route('admin.products.ai-batches.rename', $batch), ['name' => $name])
                ->assertOk()->assertJsonPath('name', null)->assertJsonPath('display_name', 'Lote de IA #'.$batch->id);
            $this->assertNull($batch->fresh()->name);
        }
    }

    public function test_name_validation_preserves_saved_names(): void
    {
        $admin = User::factory()->create(['user_type' => User::TYPE_ADMIN_MASTER]);
        $batch = ProductAiBatch::create(['name' => 'Original', 'source_type' => 'paste', 'status' => 'draft']);
        foreach ([[], ['name' => ['invalid']], ['name' => str_repeat('ñ', 121)]] as $payload) {
            $this->actingAs($admin)->patchJson(route('admin.products.ai-batches.rename', $batch), $payload)
                ->assertUnprocessable()->assertJsonValidationErrors('name');
            $this->assertSame('Original', $batch->fresh()->name);
        }
        $this->actingAs($admin)->patchJson(route('admin.products.ai-batches.rename', $batch), ['name' => str_repeat('ñ', 120)])
            ->assertOk()->assertJsonPath('name', str_repeat('ñ', 120));
    }

    public function test_customers_and_guests_cannot_rename_batches(): void
    {
        $batch = ProductAiBatch::create(['source_type' => 'paste', 'status' => 'draft']);
        $this->patchJson(route('admin.products.ai-batches.rename', $batch), ['name' => 'Changed'])->assertUnauthorized();
        $customer = User::factory()->create(['user_type' => User::TYPE_CUSTOMER]);
        $this->actingAs($customer)->patchJson(route('admin.products.ai-batches.rename', $batch), ['name' => 'Changed'])->assertForbidden();
        $this->assertNull($batch->fresh()->name);
    }

    public function test_rename_editor_escapes_saved_names_in_both_title_and_list_modes(): void
    {
        $batch = ProductAiBatch::create(['name' => '<img src=x onerror=alert(1)>', 'source_type' => 'paste', 'status' => 'draft']);
        foreach ([true, false] as $heading) {
            $html = view('components.admin.ai-batch-name', compact('batch', 'heading'))->render();
            $this->assertStringContainsString(e($batch->name), $html);
            $this->assertStringNotContainsString($batch->name, $html);
            $this->assertStringContainsString(route('admin.products.ai-batches.rename', $batch), $html);
        }
    }
}
