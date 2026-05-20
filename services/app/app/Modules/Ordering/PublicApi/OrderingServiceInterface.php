<?php

declare(strict_types=1);

namespace App\Modules\Ordering\PublicApi;

interface OrderingServiceInterface
{
    public function findOrderById(int $id): ?OrderDto;

    /** @return OrderDto[] */
    public function findOrdersByUserId(int $userId): array;
}
