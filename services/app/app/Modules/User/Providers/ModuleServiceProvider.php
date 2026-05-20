<?php

declare(strict_types=1);

namespace App\Modules\User\Providers;

use App\Modules\User\Models\User;
use App\Modules\User\Observers\UserObserver;
use App\Modules\User\PublicApi\UserServiceInterface;
use App\Modules\User\Services\CachingUserService;
use App\Modules\User\Services\UserService;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserServiceInterface::class, function ($app) {
            return new CachingUserService($app->make(UserService::class));
        });
    }

    public function boot(): void
    {
        User::observe(UserObserver::class);
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');
    }
}
