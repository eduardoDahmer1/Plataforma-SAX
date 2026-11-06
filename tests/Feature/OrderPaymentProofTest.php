<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderPaymentProof;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrderPaymentProofTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->unsignedTinyInteger('user_type')->default(User::TYPE_CUSTOMER);
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('total', 10, 2);
            $table->string('payment_method');
            $table->string('status')->default('pending');
            $table->string('payment_status')->default('pending');
            $table->string('order_number')->nullable();
            $table->timestamps();
        });
        Schema::create('order_payment_proofs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size');
            $table->timestamps();
        });
        Schema::create('system_settings', function (Blueprint $table): void {
            $table->id();
            $table->boolean('maintenance')->default(false);
            $table->timestamps();
        });
    }

    public function test_customer_can_upload_multiple_payment_proofs_to_own_order(): void
    {
        Storage::fake('local');
        $customer = User::factory()->create(['user_type' => User::TYPE_CUSTOMER]);
        $order = $this->orderFor($customer);

        $response = $this->actingAs($customer)
            ->from(route('user.orders.show', $order))
            ->post(route('user.orders.payment-proofs.store', $order), [
                'proofs' => [
                    UploadedFile::fake()->image('bancard.jpg'),
                    UploadedFile::fake()->createWithContent('pix.pdf', "%PDF-1.4\n%%EOF"),
                ],
            ]);

        $response->assertRedirect(route('user.orders.show', $order));
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseCount('order_payment_proofs', 2);

        OrderPaymentProof::query()->each(function (OrderPaymentProof $proof): void {
            Storage::disk('local')->assertExists($proof->path);
            $this->assertSame('payment-proofs/'.$proof->order_id, dirname($proof->path));
        });
    }

    public function test_customer_cannot_upload_to_another_customers_order(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create(['user_type' => User::TYPE_CUSTOMER]);
        $intruder = User::factory()->create(['user_type' => User::TYPE_CUSTOMER]);
        $order = $this->orderFor($owner);

        $this->actingAs($intruder)
            ->post(route('user.orders.payment-proofs.store', $order), [
                'proofs' => [UploadedFile::fake()->image('proof.jpg')],
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('order_payment_proofs', 0);
    }

    public function test_only_owner_or_admin_can_view_a_private_payment_proof(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create(['user_type' => User::TYPE_CUSTOMER]);
        $intruder = User::factory()->create(['user_type' => User::TYPE_CUSTOMER]);
        $admin = User::factory()->create(['user_type' => User::TYPE_ADMIN_MASTER]);
        $order = $this->orderFor($owner);
        $path = 'payment-proofs/'.$order->id.'/proof.pdf';
        Storage::disk('local')->put($path, "%PDF-1.4\n%%EOF");
        $proof = $order->paymentProofs()->create([
            'user_id' => $owner->id,
            'path' => $path,
            'original_name' => 'proof.pdf',
            'mime_type' => 'application/pdf',
            'size' => 15,
        ]);

        $this->actingAs($intruder)->get(route('payment-proofs.show', $proof))->assertForbidden();
        $this->actingAs($owner)->get(route('payment-proofs.show', $proof))->assertOk();
        $this->actingAs($admin)->get(route('payment-proofs.show', $proof))->assertOk();
    }

    private function orderFor(User $customer): Order
    {
        return Order::query()->create([
            'user_id' => $customer->id,
            'total' => 105.67,
            'payment_method' => 'bancard_v2',
            'status' => 'pending',
            'payment_status' => 'paid',
        ]);
    }
}
