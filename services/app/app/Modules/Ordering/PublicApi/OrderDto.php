<?php

declare(strict_types=1);

namespace App\Modules\Ordering\PublicApi;

readonly class OrderDto
{
    public function __construct(
        public int $id,
        public int $userId,
        public string $status,
        public float $total,
        public \DateTimeImmutable $createdAt,
    ) {}
}
