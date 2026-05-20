<?php

declare(strict_types=1);

namespace App\Modules\Catalog\PublicApi;

interface CatalogServiceInterface
{
    public function findProductById(int $id): ?ProductDto;

    public function findCategoryById(int $id): ?CategoryDto;
}
