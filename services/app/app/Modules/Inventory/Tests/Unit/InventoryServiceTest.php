<?php

declare(strict_types=1);

use App\Modules\Inventory\Database\Factories\InventoryStockFactory;
use App\Modules\Inventory\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new InventoryService;
});

it('returns the stock quantity for a product', function () {
    $stock = InventoryStockFactory::new()->create(['quantity' => 50, 'reserved' => 0]);

    $level = $this->service->getStockLevel($stock->product_id);

    expect($level)->toBe(50);
});

it('returns zero when no stock record exists for product', function () {
    $level = $this->service->getStockLevel(9999);

    expect($level)->toBe(0);
});

it('returns true when available quantity meets the requested amount', function () {
    $stock = InventoryStockFactory::new()->create(['quantity' => 10, 'reserved' => 2]);

    $available = $this->service->isAvailable($stock->product_id, 8);

    expect($available)->toBeTrue();
});

it('returns false when available quantity is less than requested', function () {
    $stock = InventoryStockFactory::new()->create(['quantity' => 10, 'reserved' => 5]);

    $available = $this->service->isAvailable($stock->product_id, 6);

    expect($available)->toBeFalse();
});

it('returns false when no stock record exists', function () {
    $available = $this->service->isAvailable(9999, 1);

    expect($available)->toBeFalse();
});

it('returns true when requesting exactly the available quantity', function () {
    $stock = InventoryStockFactory::new()->create(['quantity' => 10, 'reserved' => 3]);

    $available = $this->service->isAvailable($stock->product_id, 7);

    expect($available)->toBeTrue();
});
