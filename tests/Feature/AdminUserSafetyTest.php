<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminUserSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_suspicious_filter_surfaces_recent_unverified_accounts_and_spam_names(): void
    {
        $admin = User::factory()->create(['user_type' => User::TYPE_ADMIN_MASTER]);
        $fake = User::factory()->unverified()->create([
            'user_type' => User::TYPE_CUSTOMER,
            'name' => 'Claim bonus at https://spam.example now',
            'created_at' => now(),
        ]);
        $legitimate = User::factory()->create([
            'user_type' => User::TYPE_CUSTOMER,
            'name' => 'Cliente Verificado',
        ]);

        $this->actingAs($admin)->get(route('admin.clients.index', ['account_filter' => 'suspicious']))
            ->assertOk()
            ->assertSee($fake->email)
            ->assertDontSee($legitimate->email)
            ->assertSee('Nome com link ou promoção');
    }

    public function test_bulk_deletion_removes_only_customers_without_orders(): void
    {
        $admin = User::factory()->create(['user_type' => User::TYPE_ADMIN_MASTER]);
        $fakeOne = User::factory()->unverified()->create(['user_type' => User::TYPE_CUSTOMER]);
        $fakeTwo = User::factory()->unverified()->create(['user_type' => User::TYPE_CUSTOMER]);
        $customerWithOrder = User::factory()->unverified()->create(['user_type' => User::TYPE_CUSTOMER]);
        DB::table('orders')->insert([
            'user_id' => $customerWithOrder->id,
            'total' => 10,
            'payment_method' => 'deposito',
            'status' => 'pendente',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)->delete(route('admin.users.bulk-destroy'), [
            'user_ids' => [$fakeOne->id, $fakeTwo->id, $customerWithOrder->id, $admin->id],
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $fakeOne->id]);
        $this->assertDatabaseMissing('users', ['id' => $fakeTwo->id]);
        $this->assertDatabaseHas('users', ['id' => $customerWithOrder->id]);
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
        $this->assertDatabaseHas('orders', ['user_id' => $customerWithOrder->id]);
    }

    public function test_single_deletion_preserves_customer_order_history(): void
    {
        $admin = User::factory()->create(['user_type' => User::TYPE_ADMIN_MASTER]);
        $customer = User::factory()->create(['user_type' => User::TYPE_CUSTOMER]);
        DB::table('orders')->insert([
            'user_id' => $customer->id,
            'total' => 10,
            'payment_method' => 'deposito',
            'status' => 'pendente',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)->delete(route('admin.users.destroy', $customer))
            ->assertRedirect()->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $customer->id]);
        $this->assertDatabaseHas('orders', ['user_id' => $customer->id]);
    }
}
