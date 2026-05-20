<?php

declare(strict_types=1);

namespace App\Modules\Inventory\PublicApi;

readonly class StockDto
{
    public function __construct(
        public int $productId,
        public int $quantity,
        public bool $available,
    ) {}
}
