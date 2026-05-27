<?php

declare(strict_types=1);

use App\Modules\Inventory\PublicApi\InventoryServiceInterface;
use App\Modules\Inventory\Services\CachingInventoryService;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->inner = Mockery::mock(InventoryServiceInterface::class);
    $this->service = new CachingInventoryService($this->inner);
});

it('calls Cache::remember with key "inventory:stock:{id}" and delegates to inner', function () {
    $this->inner->expects('getStockLevel')->once()->with(1)->andReturn(42);

    Cache::shouldReceive('remember')
        ->once()
        ->withArgs(fn ($key) => $key === 'inventory:stock:1')
        ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

    $result = $this->service->getStockLevel(1);

    expect($result)->toBe(42);
});

it('calls Cache::remember with key "inventory:available:{id}:{qty}" and delegates to inner', function () {
    $this->inner->expects('isAvailable')->once()->with(1, 5)->andReturn(true);

    Cache::shouldReceive('remember')
        ->once()
        ->withArgs(fn ($key) => $key === 'inventory:available:1:5')
        ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

    $result = $this->service->isAvailable(1, 5);

    expect($result)->toBeTrue();
});

it('delegates false result for isAvailable', function () {
    $this->inner->expects('isAvailable')->once()->with(2, 100)->andReturn(false);

    Cache::shouldReceive('remember')
        ->once()
        ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

    expect($this->service->isAvailable(2, 100))->toBeFalse();
});

it('uses separate cache keys for different quantities in isAvailable', function () {
    $this->inner->expects('isAvailable')->once()->with(1, 1)->andReturn(true);
    $this->inner->expects('isAvailable')->once()->with(1, 10)->andReturn(false);

    Cache::shouldReceive('remember')
        ->twice()
        ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

    expect($this->service->isAvailable(1, 1))->toBeTrue()
        ->and($this->service->isAvailable(1, 10))->toBeFalse();
});

it('uses separate cache keys for different product ids in getStockLevel', function () {
    $this->inner->expects('getStockLevel')->once()->with(1)->andReturn(10);
    $this->inner->expects('getStockLevel')->once()->with(2)->andReturn(20);

    Cache::shouldReceive('remember')
        ->twice()
        ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

    expect($this->service->getStockLevel(1))->toBe(10)
        ->and($this->service->getStockLevel(2))->toBe(20);
});
