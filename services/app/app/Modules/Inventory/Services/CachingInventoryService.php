<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\PublicApi\InventoryServiceInterface;
use Illuminate\Support\Facades\Cache;

class CachingInventoryService implements InventoryServiceInterface
{
    public function __construct(
        private readonly InventoryServiceInterface $inner,
    ) {}

    public function getStockLevel(int $productId): int
    {
        $ttl = config('modules.cache.ttl.inventory', 120);

        return Cache::remember("inventory:stock:{$productId}", $ttl, fn () => $this->inner->getStockLevel($productId));
    }

    public function isAvailable(int $productId, int $quantity): bool
    {
        $ttl = config('modules.cache.ttl.inventory', 120);

        return Cache::remember("inventory:available:{$productId}:{$quantity}", $ttl, fn () => $this->inner->isAvailable($productId, $quantity));
    }
}
