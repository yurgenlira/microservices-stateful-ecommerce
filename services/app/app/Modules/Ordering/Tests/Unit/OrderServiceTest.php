<?php

declare(strict_types=1);

use App\Modules\Catalog\Database\Factories\ProductFactory;
use App\Modules\Inventory\PublicApi\InventoryServiceInterface;
use App\Modules\Ordering\Database\Factories\OrderFactory;
use App\Modules\Ordering\Models\Order;
use App\Modules\Ordering\Services\OrderService;
use App\Modules\User\Database\Factories\UserFactory;
use App\Modules\User\PublicApi\UserDto;
use App\Modules\User\PublicApi\UserServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->userService = Mockery::mock(UserServiceInterface::class);
    $this->inventoryService = Mockery::mock(InventoryServiceInterface::class);
    $this->service = new OrderService($this->userService, $this->inventoryService);

    ProductFactory::new()->count(5)->create();
});

it('returns the order with its customer', function () {
    $order = OrderFactory::new()->create();
    $customerDto = new UserDto(
        id: $order->user_id,
        name: 'Alice',
        email: 'alice@example.com',
        createdAt: new DateTimeImmutable,
    );

    $this->userService->expects('findById')->with($order->user_id)->andReturn($customerDto);

    $result = $this->service->getOrderWithCustomer($order->id);

    expect($result['order'])->toBeInstanceOf(Order::class)
        ->and($result['order']->id)->toBe($order->id)
        ->and($result['customer'])->toBe($customerDto);
});

it('returns customer as null when user service returns null', function () {
    $order = OrderFactory::new()->create();

    $this->userService->expects('findById')->with($order->user_id)->andReturn(null);

    $result = $this->service->getOrderWithCustomer($order->id);

    expect($result['customer'])->toBeNull();
});

it('returns true when all order items are available', function () {
    $user = UserFactory::new()->create();
    $product = ProductFactory::new()->create();
    $order = Order::create([
        'user_id' => $user->id,
        'status' => 'pending',
        'total' => 50.00,
        'shipping_address' => ['street' => '1 Main St', 'city' => 'NYC', 'country' => 'US'],
    ]);
    $order->items()->create(['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 25.00]);

    $this->inventoryService->expects('isAvailable')->once()->with($product->id, 2)->andReturn(true);

    expect($this->service->canFulfillOrder($order->id))->toBeTrue();
});

it('returns false when at least one item is unavailable', function () {
    $user = UserFactory::new()->create();
    $product = ProductFactory::new()->create();
    $order = Order::create([
        'user_id' => $user->id,
        'status' => 'pending',
        'total' => 50.00,
        'shipping_address' => ['street' => '1 Main St', 'city' => 'NYC', 'country' => 'US'],
    ]);
    $order->items()->create(['product_id' => $product->id, 'quantity' => 10, 'unit_price' => 5.00]);

    $this->inventoryService->expects('isAvailable')->once()->with($product->id, 10)->andReturn(false);

    expect($this->service->canFulfillOrder($order->id))->toBeFalse();
});

it('returns true for an order with no items', function () {
    $user = UserFactory::new()->create();
    $order = Order::create([
        'user_id' => $user->id,
        'status' => 'pending',
        'total' => 0,
        'shipping_address' => ['street' => '123 Main St', 'city' => 'NYC', 'country' => 'US'],
    ]);

    $this->inventoryService->allows('isAvailable')->never();

    expect($this->service->canFulfillOrder($order->id))->toBeTrue();
});
