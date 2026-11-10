<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductAiBatch;
use App\Models\User;
use Tests\Support\UsesProductAiDatabase;
use Tests\TestCase;

class AdminProductAiBatchManagementTest extends TestCase
{
    use UsesProductAiDatabase;

    public function test_admin_can_delete_one_batch_and_its_items(): void
    {
        $admin = User::factory()->create(['user_type' => User::TYPE_ADMIN_MASTER]);
        $product = Product::create(['sku' => 'DELETE-ONE', 'price' => 10]);
        $batch = ProductAiBatch::create(['name' => 'Lote descartable', 'source_type' => 'paste', 'status' => 'draft']);
        $item = $batch->items()->create(['product_id' => $product->id, 'submitted_sku' => $product->sku, 'status' => 'ready']);

        $this->actingAs($admin)->delete(route('admin.products.ai-batches.destroy', $batch))
            ->assertRedirect(route('admin.products.ai-batches.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('product_ai_batches', ['id' => $batch->id]);
        $this->assertDatabaseMissing('product_ai_batch_items', ['id' => $item->id]);
    }

    public function test_admin_can_delete_selected_batches_without_touching_others(): void
    {
        $admin = User::factory()->create(['user_type' => User::TYPE_ADMIN_MASTER]);
        $selected = collect([1, 2])->map(fn ($number) => ProductAiBatch::create([
            'name' => 'Eliminar '.$number, 'source_type' => 'paste', 'status' => 'completed',
        ]));
        $kept = ProductAiBatch::create(['name' => 'Conservar', 'source_type' => 'paste', 'status' => 'draft']);

        $this->actingAs($admin)->delete(route('admin.products.ai-batches.bulk-destroy'), [
            'batch_ids' => $selected->pluck('id')->all(),
        ])->assertRedirect(route('admin.products.ai-batches.index'))->assertSessionHas('success');

        $selected->each(fn (ProductAiBatch $batch) => $this->assertDatabaseMissing('product_ai_batches', ['id' => $batch->id]));
        $this->assertDatabaseHas('product_ai_batches', ['id' => $kept->id]);
    }

    public function test_new_batch_receives_a_product_style_name_and_it_remains_editable(): void
    {
        $admin = User::factory()->create(['user_type' => User::TYPE_ADMIN_MASTER]);
        Product::create(['sku' => 'SHIRT-1', 'name' => 'Camiseta blanca', 'price' => 10, 'status' => 0]);
        Product::create(['sku' => 'SHIRT-2', 'name' => 'Camiseta negra', 'price' => 10, 'status' => 0]);

        $this->actingAs($admin)->post(route('admin.products.ai-batches.preview'), [
            'skus' => "SHIRT-1\nSHIRT-2",
        ])->assertRedirect();

        $batch = ProductAiBatch::latest('id')->firstOrFail();
        $this->assertSame('Camisetas para editar · 2 productos', $batch->name);

        $this->patchJson(route('admin.products.ai-batches.rename', $batch), ['name' => 'Camisetas prioritarias'])
            ->assertOk()->assertJsonPath('display_name', 'Camisetas prioritarias');
    }
}
