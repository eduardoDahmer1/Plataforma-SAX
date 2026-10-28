<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DhlMeasurementBulkUpdateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->unsignedTinyInteger('user_type');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->string('sku');
            $table->string('name')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->integer('stock')->default(0);
            $table->string('shipping_profile', 40)->nullable();
            $table->decimal('shipping_weight_kg', 10, 3)->nullable();
            $table->decimal('shipping_length_cm', 10, 2)->nullable();
            $table->decimal('shipping_width_cm', 10, 2)->nullable();
            $table->decimal('shipping_height_cm', 10, 2)->nullable();
            $table->string('shipping_hs_code', 20)->nullable();
            $table->string('shipping_country_of_origin', 2)->nullable();
            $table->string('shipping_customs_description')->nullable();
            $table->boolean('shipping_is_dangerous_goods')->default(false);
            $table->foreignId('updated_by')->nullable();
            $table->timestamp('admin_edited_at')->nullable();
            $table->timestamps();
        });
    }

    public function test_master_admin_updates_only_selected_dhl_fields_and_records_the_audit(): void
    {
        $adminId = DB::table('users')->insertGetId([
            'name' => 'Administrador',
            'email' => 'admin@example.test',
            'password' => bcrypt('password'),
            'user_type' => User::TYPE_ADMIN_MASTER,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('products')->insert([
            [
                'id' => 10, 'sku' => 'DHL-10', 'name' => 'Produto selecionado', 'price' => 99.90, 'stock' => 7,
                'shipping_profile' => null, 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'id' => 20, 'sku' => 'DHL-20', 'name' => 'Produto não selecionado', 'price' => 49.90, 'stock' => 3,
                'shipping_profile' => null, 'created_at' => now(), 'updated_at' => now(),
            ],
        ]);

        $this->actingAs(User::query()->findOrFail($adminId))
            ->post(route('admin.dhl.measurements.products.bulk-update'), [
                'product_ids' => [10],
                'products' => [
                    10 => $this->measurements('perfume', '330300', 'fr', 'Eau de parfum', true),
                    20 => $this->measurements('furniture', '940360', 'it', 'Wood table', false),
                ],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $updated = DB::table('products')->find(10);
        $untouched = DB::table('products')->find(20);

        $this->assertSame('Produto selecionado', $updated->name);
        $this->assertEquals(99.90, $updated->price);
        $this->assertSame(7, $updated->stock);
        $this->assertSame('perfume', $updated->shipping_profile);
        $this->assertSame('330300', $updated->shipping_hs_code);
        $this->assertSame('FR', $updated->shipping_country_of_origin);
        $this->assertSame($adminId, $updated->updated_by);
        $this->assertNotNull($updated->admin_edited_at);

        $this->assertNull($untouched->shipping_profile);
        $this->assertNull($untouched->updated_by);
        $this->assertNull($untouched->admin_edited_at);
    }

    /** @return array<string, mixed> */
    private function measurements(string $profile, string $hsCode, string $origin, string $description, bool $dangerous): array
    {
        return [
            'shipping_profile' => $profile,
            'shipping_weight_kg' => 0.55,
            'shipping_length_cm' => 18,
            'shipping_width_cm' => 12,
            'shipping_height_cm' => 8,
            'shipping_hs_code' => $hsCode,
            'shipping_country_of_origin' => $origin,
            'shipping_customs_description' => $description,
            'shipping_is_dangerous_goods' => $dangerous ? 1 : 0,
        ];
    }
}
