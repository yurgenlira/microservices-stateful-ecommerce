<?php

declare(strict_types=1);

use App\Modules\Ordering\PublicApi\OrderDto;
use App\Modules\Ordering\PublicApi\OrderingServiceInterface;
use App\Modules\Ordering\Services\CachingOrderingService;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->inner = Mockery::mock(OrderingServiceInterface::class);
    $this->service = new CachingOrderingService($this->inner);
});

function makeOrderDto(int $id, int $userId): OrderDto
{
    return new OrderDto(
        id: $id,
        userId: $userId,
        status: 'pending',
        total: 99.99,
        createdAt: new DateTimeImmutable,
    );
}

it('calls Cache::remember with key "ordering:order:{id}" and delegates to inner', function () {
    $dto = makeOrderDto(1, 10);
    $this->inner->expects('findOrderById')->once()->with(1)->andReturn($dto);

    Cache::shouldReceive('remember')
        ->once()
        ->withArgs(fn ($key) => $key === 'ordering:order:1')
        ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

    $result = $this->service->findOrderById(1);

    expect($result)->toBe($dto);
});

it('returns null when inner findOrderById returns null', function () {
    $this->inner->expects('findOrderById')->once()->with(99)->andReturn(null);

    Cache::shouldReceive('remember')
        ->once()
        ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

    expect($this->service->findOrderById(99))->toBeNull();
});

it('calls Cache::remember with key "ordering:user:{id}:orders" and delegates to inner', function () {
    $dtos = [makeOrderDto(1, 5), makeOrderDto(2, 5)];
    $this->inner->expects('findOrdersByUserId')->once()->with(5)->andReturn($dtos);

    Cache::shouldReceive('remember')
        ->once()
        ->withArgs(fn ($key) => $key === 'ordering:user:5:orders')
        ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

    $result = $this->service->findOrdersByUserId(5);

    expect($result)->toBe($dtos);
});

it('delegates empty array for user with no orders', function () {
    $this->inner->expects('findOrdersByUserId')->once()->with(7)->andReturn([]);

    Cache::shouldReceive('remember')
        ->once()
        ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

    expect($this->service->findOrdersByUserId(7))->toBeArray()->toHaveCount(0);
});

it('uses separate cache keys for different order ids', function () {
    $dto1 = makeOrderDto(1, 1);
    $dto2 = makeOrderDto(2, 2);

    $this->inner->expects('findOrderById')->once()->with(1)->andReturn($dto1);
    $this->inner->expects('findOrderById')->once()->with(2)->andReturn($dto2);

    Cache::shouldReceive('remember')
        ->twice()
        ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

    expect($this->service->findOrderById(1))->toBe($dto1)
        ->and($this->service->findOrderById(2))->toBe($dto2);
});
