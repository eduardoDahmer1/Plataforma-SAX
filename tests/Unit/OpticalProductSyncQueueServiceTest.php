<?php

namespace Tests\Unit;

use App\Services\AdminNotificationService;
use App\Services\OpticalProductSyncQueueService;
use App\Services\OpticalProductSyncService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class OpticalProductSyncQueueServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'cache.default' => 'array',
            'catalog-sync.debounce_seconds' => 0,
        ]);
        Cache::clear();

        Schema::create('catalog_peer_sync_requests', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('product_id')->unique();
            $table->uuid('marker');
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->timestamp('available_at');
            $table->text('last_error')->nullable();
            $table->timestamps();
        });
    }

    public function test_repeated_edits_are_consolidated_and_a_family_is_synced_once(): void
    {
        $sync = Mockery::mock(OpticalProductSyncService::class);
        $sync->shouldReceive('enabled')->times(3)->andReturnTrue();
        $sync->shouldReceive('familyProductIds')->once()->with(10)->andReturn([10, 11]);
        $sync->shouldReceive('syncFamily')->once()->with(10)->andReturn(2);

        $notifications = Mockery::mock(AdminNotificationService::class);
        $notifications->shouldNotReceive('notifyAdmins');
        $queue = new OpticalProductSyncQueueService($sync, $notifications);

        $queue->enqueue(10);
        $firstMarker = DB::table('catalog_peer_sync_requests')->where('product_id', 10)->value('marker');
        $queue->enqueue(10);
        $queue->enqueue(11);

        $this->assertSame(2, $queue->pendingCount());
        $this->assertNotSame($firstMarker, DB::table('catalog_peer_sync_requests')->where('product_id', 10)->value('marker'));

        $result = $queue->processPending(1, 30);

        $this->assertSame(['processed' => 1, 'failed' => 0, 'pending' => 0], $result);
    }
}
