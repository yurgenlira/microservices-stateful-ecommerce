<?php

declare(strict_types=1);

use App\Modules\User\Providers\ModuleServiceProvider;
use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    ModuleServiceProvider::class,
    App\Modules\Catalog\Providers\ModuleServiceProvider::class,
    App\Modules\Ordering\Providers\ModuleServiceProvider::class,
    App\Modules\Inventory\Providers\ModuleServiceProvider::class,
];
