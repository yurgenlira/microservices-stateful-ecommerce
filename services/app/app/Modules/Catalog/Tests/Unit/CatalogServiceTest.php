<?php

declare(strict_types=1);

use App\Modules\Catalog\Database\Factories\CategoryFactory;
use App\Modules\Catalog\Database\Factories\ProductFactory;
use App\Modules\Catalog\PublicApi\CategoryDto;
use App\Modules\Catalog\PublicApi\ProductDto;
use App\Modules\Catalog\Services\CatalogService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new CatalogService;
});

it('returns a ProductDto when product exists', function () {
    $product = ProductFactory::new()->create();

    $dto = $this->service->findProductById($product->id);

    expect($dto)->toBeInstanceOf(ProductDto::class)
        ->and($dto->id)->toBe($product->id)
        ->and($dto->name)->toBe($product->name)
        ->and($dto->sku)->toBe($product->sku)
        ->and($dto->price)->toBe((float) $product->price)
        ->and($dto->categoryId)->toBe($product->category_id);
});

it('returns null when product does not exist', function () {
    $dto = $this->service->findProductById(9999);

    expect($dto)->toBeNull();
});

it('returns a CategoryDto when category exists', function () {
    $category = CategoryFactory::new()->create();

    $dto = $this->service->findCategoryById($category->id);

    expect($dto)->toBeInstanceOf(CategoryDto::class)
        ->and($dto->id)->toBe($category->id)
        ->and($dto->name)->toBe($category->name)
        ->and($dto->parentId)->toBe($category->parent_id);
});

it('returns null when category does not exist', function () {
    $dto = $this->service->findCategoryById(9999);

    expect($dto)->toBeNull();
});

it('maps parentId as null for root category', function () {
    $category = CategoryFactory::new()->create(['parent_id' => null]);

    $dto = $this->service->findCategoryById($category->id);

    expect($dto->parentId)->toBeNull();
});
