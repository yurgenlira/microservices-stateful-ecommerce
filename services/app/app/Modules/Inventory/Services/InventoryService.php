<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Models\InventoryStock;
use App\Modules\Inventory\PublicApi\InventoryServiceInterface;

class InventoryService implements InventoryServiceInterface
{
    public function getStockLevel(int $productId): int
    {
        return (int) (InventoryStock::where('product_id', $productId)->value('quantity') ?? 0);
    }

    public function isAvailable(int $productId, int $quantity): bool
    {
        $stock = InventoryStock::where('product_id', $productId)->first();

        if ($stock === null) {
            return false;
        }

        return ($stock->quantity - $stock->reserved) >= $quantity;
    }
}
