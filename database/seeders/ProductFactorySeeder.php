<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductFactorySeeder extends Seeder
{
    public function run(): void
    {
        $currentCount = Product::count();
        $targetCount = 100;
        
        if ($currentCount >= $targetCount) {
            return;
        }

        $remaining = $targetCount - $currentCount;
        
        $inStockCount = (int) round($remaining * 0.9);
        $outOfStockCount = $remaining - $inStockCount;

        Product::factory()->count($inStockCount)->inStock()->create();
        Product::factory()->count($outOfStockCount)->outOfStock()->create();
    }
}