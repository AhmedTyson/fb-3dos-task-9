<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => fake()->randomElement(['pending', 'approved', 'shipped', 'completed', 'cancelled']),
            'total' => 0,
            'shipping_address' => [
                'line1' => fake()->streetAddress(),
                'city' => fake()->city(),
                'phone' => fake()->phoneNumber()
            ],
            'payment_method' => 'cash_on_delivery',
        ];
    }
}
