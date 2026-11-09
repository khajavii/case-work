<?php

namespace Modules\Orders\Providers;

use Illuminate\Support\Facades\Gate;
use Modules\Orders\Models\Order;
use Modules\Orders\Policies\OrderPolicy;
use Modules\Orders\Repositories\Order\OrderRepository;
use Modules\Orders\Repositories\Order\OrderRepositoryInterface;
use Nwidart\Modules\Support\ModuleServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class OrdersServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Orders';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'orders';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    // protected array $commands = [];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];


    /**
     * Register any application services.
     */
    public function register(): void
    {
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
        Gate::policy(Order::class, OrderPolicy::class);
    }

}
