<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Catalog\Database\Factories\CategoryFactory;
use App\Modules\Catalog\Database\Factories\ProductFactory;
use App\Modules\Catalog\Models\Product;
use App\Modules\Inventory\Database\Factories\InventoryStockFactory;
use App\Modules\Ordering\Database\Factories\OrderFactory;
use App\Modules\User\Database\Factories\UserFactory;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $users = UserFactory::new()->count(100)->create();
        $categories = CategoryFactory::new()->count(20)->create();
        $products = ProductFactory::new()->count(500)->recycle($categories)->create();

        $products->each(fn (Product $product) => InventoryStockFactory::new()->create(['product_id' => $product->getKey()]));
        OrderFactory::new()->count(50)->recycle($users)->recycle($products)->create();
    }
}
