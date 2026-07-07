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
            'category_id' => Category::factory(),
            'name' => $name,
            'description' => $this->faker->paragraph,
            'size' => $this->faker->randomElement(['S', 'M', 'L']),
            'base_price' => $this->faker->randomFloat(2, 10, 500),
            'in_stock' => true,
            'images' => [],
        ];
    }
}
