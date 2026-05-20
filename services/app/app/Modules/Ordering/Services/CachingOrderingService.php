<?php

declare(strict_types=1);

namespace App\Modules\Ordering\Services;

use App\Modules\Ordering\PublicApi\OrderDto;
use App\Modules\Ordering\PublicApi\OrderingServiceInterface;
use Illuminate\Support\Facades\Cache;

class CachingOrderingService implements OrderingServiceInterface
{
    public function __construct(
        private readonly OrderingServiceInterface $inner,
    ) {}

    public function findOrderById(int $id): ?OrderDto
    {
        $ttl = config('modules.cache.ttl.ordering', 60);

        return Cache::remember("ordering:order:{$id}", $ttl, fn () => $this->inner->findOrderById($id));
    }

    /** @return OrderDto[] */
    public function findOrdersByUserId(int $userId): array
    {
        $ttl = config('modules.cache.ttl.ordering', 60);

        return Cache::remember("ordering:user:{$userId}:orders", $ttl, fn () => $this->inner->findOrdersByUserId($userId));
    }
}
