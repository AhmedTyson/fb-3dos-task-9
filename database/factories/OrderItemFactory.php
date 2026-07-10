<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'product_id' => Product::inRandomOrder()->value('id'),
            'quantity' => fake()->numberBetween(1, 5),
            'unit_price' => fake()->randomFloat(2, 10, 500),
            'subtotal' => 0, // calculated on create
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (OrderItem $item) {
            $item->subtotal = $item->unit_price * $item->quantity;
            $item->saveQuietly();
        });
    }
}
