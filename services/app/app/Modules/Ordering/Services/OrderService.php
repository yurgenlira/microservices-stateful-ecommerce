<?php

declare(strict_types=1);

namespace App\Modules\Ordering\Services;

use App\Modules\Inventory\PublicApi\InventoryServiceInterface;
use App\Modules\Ordering\Models\Order;
use App\Modules\User\PublicApi\UserServiceInterface;

class OrderService
{
    public function __construct(
        private readonly UserServiceInterface $userService,
        private readonly InventoryServiceInterface $inventoryService,
    ) {}

    /** @return array{order: Order, customer: mixed} */
    public function getOrderWithCustomer(int $orderId): array
    {
        $order = Order::with('items')->findOrFail($orderId);
        $customer = $this->userService->findById($order->user_id);

        return [
            'order' => $order,
            'customer' => $customer,
        ];
    }

    public function canFulfillOrder(int $orderId): bool
    {
        $order = Order::with('items')->findOrFail($orderId);

        foreach ($order->items as $item) {
            if (! $this->inventoryService->isAvailable($item->product_id, $item->quantity)) {
                return false;
            }
        }

        return true;
    }
}
