<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            'Keyboards' => [
                ['name' => 'Logitech MX Keys', 'description' => 'Advanced wireless illuminated keyboard with smart backlit keys that adjust to your environment.', 'size' => 'Full Size', 'base_price' => 499.00, 'in_stock' => true, 'images' => ['/storage/products/keyboards/mx-keys.jpg']],
                ['name' => 'Keychron K2 Wireless', 'description' => 'Compact 75% wireless mechanical keyboard with hot-swappable switches and RGB backlight.', 'size' => '75%', 'base_price' => 350.00, 'in_stock' => true, 'images' => ['/storage/products/keyboards/keychron-k2.jpg']],
                ['name' => 'Rapoo E9700M', 'description' => 'Ultra-slim multi-mode wireless keyboard supporting Bluetooth and 2.4G, up to 3 devices.', 'size' => 'Full Size', 'base_price' => 220.00, 'in_stock' => true, 'images' => ['/storage/products/keyboards/rapoo-e9700m.jpg']],
            ],
            'Mice' => [
                ['name' => 'Logitech MX Master 3', 'description' => 'High-precision wireless mouse with MagSpeed electromagnetic scrolling and ergonomic design.', 'size' => 'Standard', 'base_price' => 450.00, 'in_stock' => true, 'images' => ['/storage/products/mice/mx-master-3.jpg']],
                ['name' => 'Razer DeathAdder V3', 'description' => 'Ultra-lightweight ergonomic gaming mouse with 30K DPI optical sensor.', 'size' => 'Standard', 'base_price' => 380.00, 'in_stock' => true, 'images' => ['/storage/products/mice/deathadder-v3.jpg']],
                ['name' => 'Xiaomi Wireless Mouse Lite', 'description' => 'Compact silent wireless mouse with 1000 DPI sensor, ideal for everyday office use.', 'size' => 'Compact', 'base_price' => 80.00, 'in_stock' => true, 'images' => ['/storage/products/mice/xiaomi-lite.jpg']],
            ],
            'Headsets' => [
                ['name' => 'HyperX Cloud II', 'description' => '7.1 virtual surround sound gaming headset with memory foam ear cushions and detachable mic.', 'size' => 'One Size', 'base_price' => 520.00, 'in_stock' => true, 'images' => ['/storage/products/headsets/hyperx-cloud-ii.jpg']],
                ['name' => 'Sony WH-1000XM5', 'description' => 'Industry-leading noise cancelling wireless headphones with 30-hour battery life.', 'size' => 'One Size', 'base_price' => 1099.00, 'in_stock' => true, 'images' => ['/storage/products/headsets/sony-wh1000xm5.jpg']],
                ['name' => 'Jabra Evolve2 55', 'description' => 'Professional wireless headset with ANC, certified for Microsoft Teams and Zoom.', 'size' => 'One Size', 'base_price' => 850.00, 'in_stock' => false, 'images' => ['/storage/products/headsets/jabra-evolve2-55.jpg']],
            ],
            'Monitors' => [
                ['name' => 'LG UltraWide 34WN80C', 'description' => '34" curved UltraWide QHD IPS monitor with USB-C connectivity and HDR10 support.', 'size' => '34 inch', 'base_price' => 3200.00, 'in_stock' => true, 'images' => ['/storage/products/monitors/lg-34wn80c.jpg']],
                ['name' => 'Samsung Odyssey G5 27"', 'description' => '27" 1440p 165Hz curved gaming monitor with 1ms response time and AMD FreeSync Premium.', 'size' => '27 inch', 'base_price' => 2100.00, 'in_stock' => true, 'images' => ['/storage/products/monitors/samsung-g5.jpg']],
            ],
            'Webcams' => [
                ['name' => 'Logitech C920 HD Pro', 'description' => 'Full HD 1080p/30fps webcam with automatic light correction and dual stereo microphones.', 'size' => 'Standard', 'base_price' => 350.00, 'in_stock' => true, 'images' => ['/storage/products/webcams/c920.jpg']],
                ['name' => 'Razer Kiyo Pro', 'description' => 'Full HD streaming webcam with adaptive light sensor and wide-angle lens.', 'size' => 'Standard', 'base_price' => 480.00, 'in_stock' => true, 'images' => ['/storage/products/webcams/kiyo-pro.jpg']],
            ],
            'USB Hubs' => [
                ['name' => 'Anker 10-Port USB 3.0 Hub', 'description' => '10-port powered USB 3.0 hub with 60W charging port and individual LED switches.', 'size' => 'Standard', 'base_price' => 250.00, 'in_stock' => true, 'images' => ['/storage/products/hubs/anker-10port.jpg']],
                ['name' => 'Ugreen USB-C Hub 7-in-1', 'description' => '7-in-1 USB-C hub with 4K HDMI, 100W PD, USB 3.0 x3, SD/TF card reader.', 'size' => 'Compact', 'base_price' => 180.00, 'in_stock' => true, 'images' => ['/storage/products/hubs/ugreen-7in1.jpg']],
            ],
            'Cables & Adapters' => [
                ['name' => 'Ugreen USB-C to HDMI 2.1 Cable', 'description' => '2m USB-C to HDMI 2.1 cable supporting 8K/60Hz and 4K/144Hz output.', 'size' => '2m', 'base_price' => 95.00, 'in_stock' => true, 'images' => ['/storage/products/cables/usbc-hdmi-2m.jpg']],
                ['name' => 'Baseus 100W GaN Charger', 'description' => 'Compact 100W GaN fast charger with 2x USB-C + 1x USB-A ports.', 'size' => 'Compact', 'base_price' => 220.00, 'in_stock' => true, 'images' => ['/storage/products/cables/baseus-100w.jpg']],
            ],
        ];

        foreach ($products as $categoryName => $items) {
            $category = Category::where('name', $categoryName)->firstOrFail();

            foreach ($items as $item) {
                Product::firstOrCreate(
                    ['name' => $item['name']],
                    array_merge($item, ['category_id' => $category->id])
                );
            }
        }
    }
}
