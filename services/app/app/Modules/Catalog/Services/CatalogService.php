<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Services;

use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\PublicApi\CatalogServiceInterface;
use App\Modules\Catalog\PublicApi\CategoryDto;
use App\Modules\Catalog\PublicApi\ProductDto;

class CatalogService implements CatalogServiceInterface
{
    public function findProductById(int $id): ?ProductDto
    {
        $product = Product::find($id);

        return $product ? $this->toProductDto($product) : null;
    }

    public function findCategoryById(int $id): ?CategoryDto
    {
        $category = Category::find($id);

        return $category ? $this->toCategoryDto($category) : null;
    }

    private function toProductDto(Product $product): ProductDto
    {
        return new ProductDto(
            id: $product->id,
            name: $product->name,
            sku: $product->sku,
            price: (float) $product->price,
            categoryId: $product->category_id,
        );
    }

    private function toCategoryDto(Category $category): CategoryDto
    {
        return new CategoryDto(
            id: $category->id,
            name: $category->name,
            parentId: $category->parent_id,
        );
    }
}
