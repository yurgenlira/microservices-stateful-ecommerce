<?php

declare(strict_types=1);

namespace App\Modules\Ordering\Providers;

use App\Modules\Ordering\PublicApi\OrderingServiceInterface;
use App\Modules\Ordering\Services\CachingOrderingService;
use App\Modules\Ordering\Services\OrderingService;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OrderingServiceInterface::class, function ($app) {
            return new CachingOrderingService($app->make(OrderingService::class));
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');
    }
}
