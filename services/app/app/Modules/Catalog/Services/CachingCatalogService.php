<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Services;

use App\Modules\Catalog\PublicApi\CatalogServiceInterface;
use App\Modules\Catalog\PublicApi\CategoryDto;
use App\Modules\Catalog\PublicApi\ProductDto;
use Illuminate\Support\Facades\Cache;

class CachingCatalogService implements CatalogServiceInterface
{
    public function __construct(
        private readonly CatalogServiceInterface $inner,
    ) {}

    public function findProductById(int $id): ?ProductDto
    {
        $ttl = config('modules.cache.ttl.catalog', 600);

        return Cache::remember("catalog:product:{$id}", $ttl, fn () => $this->inner->findProductById($id));
    }

    public function findCategoryById(int $id): ?CategoryDto
    {
        $ttl = config('modules.cache.ttl.catalog', 600);

        return Cache::remember("catalog:category:{$id}", $ttl, fn () => $this->inner->findCategoryById($id));
    }
}
