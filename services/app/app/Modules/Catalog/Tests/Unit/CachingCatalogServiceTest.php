<?php

declare(strict_types=1);

use App\Modules\Catalog\PublicApi\CatalogServiceInterface;
use App\Modules\Catalog\PublicApi\CategoryDto;
use App\Modules\Catalog\PublicApi\ProductDto;
use App\Modules\Catalog\Services\CachingCatalogService;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->inner = Mockery::mock(CatalogServiceInterface::class);
    $this->service = new CachingCatalogService($this->inner);
});

it('calls Cache::remember with key "catalog:product:{id}" and delegates to inner', function () {
    $dto = new ProductDto(id: 1, name: 'Widget', sku: 'SKU-AB-0001', price: 9.99, categoryId: 1);
    $this->inner->expects('findProductById')->once()->with(1)->andReturn($dto);

    Cache::shouldReceive('remember')
        ->once()
        ->withArgs(fn ($key) => $key === 'catalog:product:1')
        ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

    $result = $this->service->findProductById(1);

    expect($result)->toBe($dto);
});

it('returns null when inner findProductById returns null', function () {
    $this->inner->expects('findProductById')->once()->with(99)->andReturn(null);

    Cache::shouldReceive('remember')
        ->once()
        ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

    expect($this->service->findProductById(99))->toBeNull();
});

it('calls Cache::remember with key "catalog:category:{id}" and delegates to inner', function () {
    $dto = new CategoryDto(id: 5, name: 'Electronics', parentId: null);
    $this->inner->expects('findCategoryById')->once()->with(5)->andReturn($dto);

    Cache::shouldReceive('remember')
        ->once()
        ->withArgs(fn ($key) => $key === 'catalog:category:5')
        ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

    $result = $this->service->findCategoryById(5);

    expect($result)->toBe($dto);
});

it('uses separate cache keys for products and categories', function () {
    $product = new ProductDto(id: 1, name: 'Widget', sku: 'SKU-AB-0001', price: 9.99, categoryId: 1);
    $category = new CategoryDto(id: 1, name: 'Electronics', parentId: null);

    $this->inner->expects('findProductById')->once()->with(1)->andReturn($product);
    $this->inner->expects('findCategoryById')->once()->with(1)->andReturn($category);

    Cache::shouldReceive('remember')
        ->twice()
        ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

    $this->service->findProductById(1);
    $this->service->findCategoryById(1);
});
