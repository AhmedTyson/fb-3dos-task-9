<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::where('role', 'customer')->take(20)->get();
        $products = Product::inRandomOrder()->take(50)->get();

        foreach ($customers as $customer) {
            $orders = Order::factory()->count(2)->for($customer)->create();

            foreach ($orders as $order) {
                $orderProducts = $products->random(2);

                foreach ($orderProducts as $product) {
                    OrderItem::factory()
                        ->for($order)
                        ->for($product)
                        ->create();
                }

                $order->update(['total' => $order->items()->sum('subtotal')]);
            }
        }
    }
}
