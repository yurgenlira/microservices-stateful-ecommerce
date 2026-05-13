<?php

declare(strict_types=1);

namespace App\Modules\Ordering\Database\Factories;

use App\Modules\Catalog\Models\Product;
use App\Modules\Ordering\Models\Order;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => $this->faker->randomElement([
                'pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled',
            ]),
            'total' => 0,
            'shipping_address' => [
                'street' => $this->faker->streetAddress(),
                'city' => $this->faker->city(),
                'country' => $this->faker->countryCode(),
            ],
        ];
    }

    public function configure(): static
    {
        $this->afterCreating(function (Order $order) {
            $products = Product::inRandomOrder()->limit($this->faker->numberBetween(1, 5))->get();
            $total = 0;

            foreach ($products as $product) {
                $qty = $this->faker->numberBetween(1, 5);
                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $product->price,
                ]);
                $total += $qty * (float) $product->price;
            }

            $order->update(['total' => $total]);
        });

        return $this;
    }
}
