<?php

namespace App\Providers;

use App\Models\Contact;
use App\Models\AbandonedCart;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Cupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Observers\AbandonedCartObserver;
use App\Observers\BrandObserver;
use App\Observers\CategoryObserver;
use App\Observers\ContactObserver;
use App\Observers\CuponObserver;
use App\Observers\OrderObserver;
use App\Observers\ProductObserver;
use App\Observers\UserObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        Order::observe(OrderObserver::class);
        Contact::observe(ContactObserver::class);
        User::observe(UserObserver::class);
        Product::observe(ProductObserver::class);
        AbandonedCart::observe(AbandonedCartObserver::class);
        Category::observe(CategoryObserver::class);
        Brand::observe(BrandObserver::class);
        Cupon::observe(CuponObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
