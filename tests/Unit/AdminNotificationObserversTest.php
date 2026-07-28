<?php

namespace Tests\Unit;

use App\Models\Contact;
use App\Models\Order;
use App\Models\User;
use App\Observers\ContactObserver;
use App\Observers\OrderObserver;
use App\Observers\UserObserver;
use App\Services\AdminNotificationService;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class AdminNotificationObserversTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_order_observer_requests_a_notification_for_admins(): void
    {
        $service = Mockery::mock(AdminNotificationService::class);
        $service->shouldReceive('notifyAdmins')
            ->once()
            ->with(
                'new_order',
                'Novo pedido',
                'O pedido #ABC123 foi recebido.',
                '/admin/orders/42',
                ['order_id' => 42],
            );

        $order = new Order;
        $order->setAttribute('id', 42);
        $order->setAttribute('order_number', 'ABC123');

        (new OrderObserver($service))->created($order);
    }

    public function test_contact_observer_requests_a_contact_notification(): void
    {
        $service = Mockery::mock(AdminNotificationService::class);
        $service->shouldReceive('notifyAdmins')
            ->once()
            ->with(
                'new_contact',
                'Novo contato',
                'Ana enviou uma mensagem pelo formulário.',
                '/admin/contatos',
                ['contact_id' => 7, 'contact_type' => 1],
            );

        $contact = new Contact;
        $contact->setAttribute('id', 7);
        $contact->setAttribute('name', 'Ana');
        $contact->setAttribute('contact_type', 1);

        (new ContactObserver($service))->created($contact);
    }

    public function test_contact_observer_ignores_newsletter_subscriptions(): void
    {
        $service = Mockery::mock(AdminNotificationService::class);
        $service->shouldNotReceive('notifyAdmins');

        $contact = new Contact;
        $contact->setAttribute('id', 8);
        $contact->setAttribute('name', 'Bruno');
        $contact->setAttribute('contact_type', 3);

        (new ContactObserver($service))->created($contact);
    }

    public function test_user_registration_requests_a_notification_for_admins(): void
    {
        $service = Mockery::mock(AdminNotificationService::class);
        $service->shouldReceive('notifyAdmins')
            ->once()
            ->with(
                'new_user',
                'Novo usuário',
                'Ana criou uma conta.',
                '/admin/clients',
                ['user_id' => 11],
            );

        $user = new User;
        $user->setAttribute('id', 11);
        $user->setAttribute('name', 'Ana');

        (new UserObserver($service))->created($user);
    }
}
