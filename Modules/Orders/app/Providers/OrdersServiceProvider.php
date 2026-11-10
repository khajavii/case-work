<?php

namespace Modules\Orders\Providers;

use Illuminate\Support\Facades\Gate;
use Modules\Orders\Models\Order;
use Modules\Orders\Policies\OrderPolicy;
use Modules\Orders\Repositories\Order\OrderRepository;
use Modules\Orders\Repositories\Order\OrderRepositoryInterface;
use Nwidart\Modules\Support\ModuleServiceProvider;

class OrdersServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Orders';

    protected string $nameLower = 'orders';

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        parent::register();

        $this->app->bind(
            OrderRepositoryInterface::class,
            OrderRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();

        Gate::policy(Order::class, OrderPolicy::class);
    }
}
