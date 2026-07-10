<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->words(3, true);
        return [
            'category_id' => Category::inRandomOrder()->value('id'),
            'name' => $name,
            'description' => $this->faker->paragraph,
            'size' => $this->faker->randomElement(['S', 'M', 'L', 'XL', 'Standard', 'Compact', 'Full Size', '75%', 'One Size', '27 inch', '34 inch', '2m', 'Standard']),
            'base_price' => $this->faker->randomFloat(2, 10, 500),
            'stock' => $this->faker->numberBetween(20, 100),
            'in_stock' => true,
            'images' => [
                'https://picsum.photos/seed/' . Str::slug($name) . '/400/300.jpg',
            ],
        ];
    }

    public function inStock(): static
    {
        return $this->state(fn() => [
            'in_stock' => true,
            'stock' => fake()->numberBetween(20, 100),
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn() => [
            'in_stock' => false,
            'stock' => 0,
        ]);
    }
}
