<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNotificationManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_archive_and_restore_an_owned_notification(): void
    {
        $admin = User::factory()->create(['user_type' => User::TYPE_ADMIN_MASTER]);
        $notification = $this->notificationFor($admin);

        $this->actingAs($admin)
            ->postJson(route('admin.notifications.archive', $notification))
            ->assertOk();

        $notification->refresh();
        $this->assertNotNull($notification->archived_at);
        $this->assertNotNull($notification->read_at);

        $this->actingAs($admin)
            ->postJson(route('admin.notifications.restore', $notification))
            ->assertOk();

        $this->assertNull($notification->refresh()->archived_at);
    }

    public function test_bulk_delete_only_removes_notifications_owned_by_current_admin(): void
    {
        $admin = User::factory()->create(['user_type' => User::TYPE_ADMIN_MASTER]);
        $otherAdmin = User::factory()->create(['user_type' => User::TYPE_ADMIN_MASTER]);
        $owned = $this->notificationFor($admin);
        $notOwned = $this->notificationFor($otherAdmin);

        $this->actingAs($admin)->postJson(route('admin.notifications.bulk'), [
            'action' => 'delete',
            'notification_ids' => [$owned->id, $notOwned->id],
        ])->assertOk()->assertJson(['updated' => 1]);

        $this->assertDatabaseMissing('notifications', ['id' => $owned->id]);
        $this->assertDatabaseHas('notifications', ['id' => $notOwned->id]);
    }

    public function test_mark_all_as_read_does_not_change_archived_notifications(): void
    {
        $admin = User::factory()->create(['user_type' => User::TYPE_ADMIN_MASTER]);
        $active = $this->notificationFor($admin);
        $archived = $this->notificationFor($admin, ['archived_at' => now()]);

        $this->actingAs($admin)
            ->postJson(route('admin.notifications.read-all'))
            ->assertOk()
            ->assertJson(['updated' => 1]);

        $this->assertNotNull($active->refresh()->read_at);
        $this->assertNull($archived->refresh()->read_at);
    }

    private function notificationFor(User $user, array $attributes = []): Notification
    {
        return Notification::query()->create(array_merge([
            'user_id' => $user->id,
            'type' => 'integration_failed',
            'title' => 'Falha na integração',
            'message' => 'Consulte os detalhes no painel.',
            'action_url' => '/admin',
        ], $attributes));
    }
}
