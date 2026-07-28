<?php

namespace Tests\Unit;

use App\Models\Notification;
use PHPUnit\Framework\TestCase;

class NotificationModelTest extends TestCase
{
    public function test_notification_defines_its_admin_fields_and_casts(): void
    {
        $notification = new Notification;

        $this->assertSame([
            'user_id',
            'type',
            'title',
            'message',
            'action_url',
            'data',
            'read_at',
        ], $notification->getFillable());

        $this->assertSame('array', $notification->getCasts()['data']);
        $this->assertSame('datetime', $notification->getCasts()['read_at']);
    }
}
