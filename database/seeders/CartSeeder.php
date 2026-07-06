<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Database\Seeder;

class CartSeeder extends Seeder
{
    public function run(): void
    {
        $testCustomer = User::where('email', 'ahmed@example.com')->first();
        $products = Product::inRandomOrder()->take(3)->get();

        if ($testCustomer && $products->count() === 3) {
            $cart = Cart::firstOrCreate(['user_id' => $testCustomer->id]);

            foreach ($products as $product) {
                CartItem::factory()
                    ->for($cart)
                    ->for($product)
                    ->create();
            }
        }
    }
}
