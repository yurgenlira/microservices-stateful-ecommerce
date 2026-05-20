<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Providers;

use App\Modules\Catalog\PublicApi\CatalogServiceInterface;
use App\Modules\Catalog\Services\CachingCatalogService;
use App\Modules\Catalog\Services\CatalogService;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CatalogServiceInterface::class, function ($app) {
            return new CachingCatalogService($app->make(CatalogService::class));
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');
    }
}
