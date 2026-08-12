<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderNote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderNotesTest extends TestCase
{
    use RefreshDatabase;

    public function test_master_admin_can_publish_a_preset_note_and_notify_the_customer(): void
    {
        $admin = User::factory()->create(['user_type' => User::TYPE_ADMIN_MASTER]);
        $customer = User::factory()->create(['user_type' => User::TYPE_CUSTOMER]);
        $order = Order::query()->create([
            'user_id' => $customer->id,
            'total' => 100,
            'payment_method' => 'deposito',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.orders.show', $order))
            ->post(route('admin.orders.notes.store', $order), [
                'note_type' => 'picking',
            ]);

        $response->assertRedirect(route('admin.orders.show', $order));
        $this->assertDatabaseHas('order_notes', [
            'order_id' => $order->id,
            'created_by' => $admin->id,
            'type' => 'picking',
            'message' => OrderNote::messageForType('picking'),
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $customer->id,
            'type' => 'customer_order_note',
            'action_url' => "/orders/{$order->id}",
        ]);
    }

    public function test_other_note_requires_and_stores_the_custom_text(): void
    {
        $admin = User::factory()->create(['user_type' => User::TYPE_ADMIN_MASTER]);
        $customer = User::factory()->create(['user_type' => User::TYPE_CUSTOMER]);
        $order = Order::query()->create([
            'user_id' => $customer->id,
            'total' => 100,
            'payment_method' => 'deposito',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.orders.notes.store', $order), [
                'note_type' => OrderNote::TYPE_OTHER,
                'note_custom' => 'A entrega foi reagendada para sexta-feira.',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('order_notes', [
            'order_id' => $order->id,
            'type' => OrderNote::TYPE_OTHER,
            'message' => 'A entrega foi reagendada para sexta-feira.',
        ]);
    }
}
