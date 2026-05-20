<?php

declare(strict_types=1);

namespace App\Modules\Inventory\PublicApi;

interface InventoryServiceInterface
{
    public function getStockLevel(int $productId): int;

    public function isAvailable(int $productId, int $quantity): bool;
}
