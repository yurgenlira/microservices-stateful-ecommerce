<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Providers;

use App\Modules\Inventory\PublicApi\InventoryServiceInterface;
use App\Modules\Inventory\Services\CachingInventoryService;
use App\Modules\Inventory\Services\InventoryService;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(InventoryServiceInterface::class, function ($app) {
            return new CachingInventoryService($app->make(InventoryService::class));
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');
    }
}
