<?php

declare(strict_types=1);

namespace App\Modules\Catalog\PublicApi;

readonly class ProductDto
{
    public function __construct(
        public int $id,
        public string $name,
        public string $sku,
        public float $price,
        public int $categoryId,
    ) {}
}
