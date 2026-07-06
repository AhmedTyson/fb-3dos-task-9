<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id'   => Order::factory(),
            'product_id' => fn() => Product::inRandomOrder()->first()->id,
            'quantity'   => fake()->numberBetween(1, 4),
            'unit_price' => function (array $attributes) {
                return Product::find($attributes['product_id'])->base_price;
            },
            'subtotal' => function (array $attributes) {
                return $attributes['quantity'] * $attributes['unit_price'];
            },
        ];
    }
}
