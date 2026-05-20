<?php

declare(strict_types=1);

namespace App\Modules\Ordering\Services;

use App\Modules\Ordering\Models\Order;
use App\Modules\Ordering\PublicApi\OrderDto;
use App\Modules\Ordering\PublicApi\OrderingServiceInterface;

class OrderingService implements OrderingServiceInterface
{
    public function findOrderById(int $id): ?OrderDto
    {
        $order = Order::find($id);

        return $order ? $this->toDto($order) : null;
    }

    /** @return OrderDto[] */
    public function findOrdersByUserId(int $userId): array
    {
        return Order::where('user_id', $userId)
            ->get()
            ->map(fn (Order $order) => $this->toDto($order))
            ->all();
    }

    private function toDto(Order $order): OrderDto
    {
        return new OrderDto(
            id: $order->id,
            userId: $order->user_id,
            status: $order->status,
            total: (float) $order->total,
            createdAt: new \DateTimeImmutable($order->created_at->toDateTimeString()),
        );
    }
}
