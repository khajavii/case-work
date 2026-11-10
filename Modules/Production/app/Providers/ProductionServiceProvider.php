<?php

namespace Modules\Production\Providers;

use Illuminate\Support\Facades\Gate;
use Modules\Production\Repositories\JobStatusHistory\JobStatusHistoryRepository;
use Modules\Production\Repositories\JobStatusHistory\JobStatusHistoryRepositoryInterface;
use Modules\Production\Repositories\ProductionJob\ProductionJobRepository;
use Modules\Production\Repositories\ProductionJob\ProductionJobRepositoryInterface;
use Modules\Production\Models\ProductionJob;
use Modules\Production\Policies\ProductionJobPolicy;
use Nwidart\Modules\Support\ModuleServiceProvider;

class ProductionServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Production';

    protected string $nameLower = 'production';

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
            ProductionJobRepositoryInterface::class,
            ProductionJobRepository::class
        );

        $this->app->bind(
            JobStatusHistoryRepositoryInterface::class,
            JobStatusHistoryRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();

        Gate::policy(
            ProductionJob::class,
            ProductionJobPolicy::class
        );
    }
}
