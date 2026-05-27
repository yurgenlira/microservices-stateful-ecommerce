<?php

declare(strict_types=1);

use App\Modules\Catalog\Database\Factories\ProductFactory;
use App\Modules\Ordering\Database\Factories\OrderFactory;
use App\Modules\Ordering\PublicApi\OrderDto;
use App\Modules\Ordering\Services\OrderingService;
use App\Modules\User\Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new OrderingService;
    ProductFactory::new()->count(5)->create();
});

it('returns an OrderDto when order exists by id', function () {
    $order = OrderFactory::new()->create();

    $dto = $this->service->findOrderById($order->id);

    expect($dto)->toBeInstanceOf(OrderDto::class)
        ->and($dto->id)->toBe($order->id)
        ->and($dto->userId)->toBe($order->user_id)
        ->and($dto->status)->toBe($order->status)
        ->and($dto->total)->toBe((float) $order->total);
});

it('returns null when order does not exist', function () {
    $dto = $this->service->findOrderById(9999);

    expect($dto)->toBeNull();
});

it('maps createdAt as DateTimeImmutable', function () {
    $order = OrderFactory::new()->create();

    $dto = $this->service->findOrderById($order->id);

    expect($dto->createdAt)->toBeInstanceOf(DateTimeImmutable::class);
});

it('returns all orders for a user', function () {
    $user = UserFactory::new()->create();
    OrderFactory::new()->count(3)->create(['user_id' => $user->id]);

    $results = $this->service->findOrdersByUserId($user->id);

    expect($results)->toHaveCount(3)
        ->each->toBeInstanceOf(OrderDto::class);
});

it('returns empty array when user has no orders', function () {
    $user = UserFactory::new()->create();

    $results = $this->service->findOrdersByUserId($user->id);

    expect($results)->toBeArray()->toHaveCount(0);
});

it('only returns orders belonging to the specified user', function () {
    $user1 = UserFactory::new()->create();
    $user2 = UserFactory::new()->create();

    OrderFactory::new()->count(2)->create(['user_id' => $user1->id]);
    OrderFactory::new()->count(3)->create(['user_id' => $user2->id]);

    $results = $this->service->findOrdersByUserId($user1->id);

    expect($results)->toHaveCount(2)
        ->and(collect($results)->pluck('userId')->unique()->all())->toBe([$user1->id]);
});
