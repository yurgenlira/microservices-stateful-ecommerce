<?php

declare(strict_types=1);

namespace App\Modules\Catalog\PublicApi;

readonly class CategoryDto
{
    public function __construct(
        public int $id,
        public string $name,
        public ?int $parentId,
    ) {}
}
