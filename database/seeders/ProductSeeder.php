<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = array (
  'Keyboards' => 
  array (
    0 => 
    array (
      'name' => 'Logitech MX Keys',
      'description' => 'Advanced wireless illuminated keyboard with smart backlit keys that adjust to your environment.',
      'size' => 'Full Size',
      'base_price' => 499.0,
      'stock' => 50,
      'in_stock' => true,
      'images' => 
      array (
        0 => '/storage/products/logitech-mx-keys.jpg',
      ),
    ),
    1 => 
    array (
      'name' => 'Keychron K2 Wireless',
      'description' => 'Compact 75% wireless mechanical keyboard with hot-swappable switches and RGB backlight.',
      'size' => '75%',
      'base_price' => 350.0,
      'stock' => 30,
      'in_stock' => true,
      'images' => 
      array (
        0 => '/storage/products/keychron-k2-wireless.jpg',
      ),
    ),
    2 => 
    array (
      'name' => 'Rapoo E9700M',
      'description' => 'Ultra-slim multi-mode wireless keyboard supporting Bluetooth and 2.4G, up to 3 devices.',
      'size' => 'Full Size',
      'base_price' => 220.0,
      'stock' => 25,
      'in_stock' => true,
      'images' => 
      array (
        0 => '/storage/products/rapoo-e9700m.jpg',
      ),
    ),
    3 => 
    array (
      'name' => 'Logitech G Pro X',
      'description' => 'Tournament-grade mechanical keyboard with hot-swappable GX switches.',
      'size' => 'Tenkeyless',
      'base_price' => 550.0,
      'stock' => 20,
      'in_stock' => true,
      'images' => 
      array (
        0 => '/storage/products/logitech-g-pro-x.jpg',
      ),
    ),
    4 => 
    array (
      'name' => 'Ducky One 2 Mini',
      'description' => '60% mechanical keyboard with Cherry MX RGB switches and PBT keycaps.',
      'size' => '60%',
      'base_price' => 420.0,
      'stock' => 15,
      'in_stock' => true,
      'images' => 
      array (
        0 => '/storage/products/ducky-one-2-mini.jpg',
      ),
    ),
  ),
  'Mice' => 
  array (
    0 => 
    array (
      'name' => 'Logitech MX Master 3',
      'description' => 'High-precision wireless mouse with MagSpeed electromagnetic scrolling and ergonomic design.',
      'size' => 'Standard',
      'base_price' => 450.0,
      'stock' => 40,
      'in_stock' => true,
      'images' => 
      array (
        0 => '/storage/products/logitech-mx-master-3.jpg',
      ),
    ),
    1 => 
    array (
      'name' => 'Razer DeathAdder V3',
      'description' => 'Ultra-lightweight ergonomic gaming mouse with 30K DPI optical sensor.',
      'size' => 'Standard',
      'base_price' => 380.0,
      'stock' => 35,
      'in_stock' => true,
      'images' => 
      array (
        0 => '/storage/products/razer-deathadder-v3.jpg',
      ),
    ),
    2 => 
    array (
      'name' => 'Xiaomi Wireless Mouse Lite',
      'description' => 'Compact silent wireless mouse with 1000 DPI sensor, ideal for everyday office use.',
      'size' => 'Compact',
      'base_price' => 80.0,
      'stock' => 60,
      'in_stock' => true,
      'images' => 
      array (
        0 => '/storage/products/xiaomi-wireless-mouse-lite.jpg',
      ),
    ),
    3 => 
    array (
      'name' => 'Logitech G Pro Wireless',
      'description' => 'Esports-grade wireless gaming mouse with HERO 25K sensor.',
      'size' => 'Standard',
      'base_price' => 520.0,
      'stock' => 25,
      'in_stock' => true,
      'images' => 
      array (
        0 => '/storage/products/logitech-g-pro-wireless.jpg',
      ),
    ),
    4 => 
    array (
      'name' => 'Zowie EC2-C',
      'description' => 'Ergonomic gaming mouse with 3360 sensor and 24-step scroll wheel.',
      'size' => 'Medium',
      'base_price' => 290.0,
      'stock' => 20,
      'in_stock' => true,
      'images' => 
      array (
        0 => '/storage/products/zowie-ec2-c.jpg',
      ),
    ),
  ),
  'Headsets' => 
  array (
    0 => 
    array (
      'name' => 'HyperX Cloud II',
      'description' => '7.1 virtual surround sound gaming headset with memory foam ear cushions and detachable mic.',
      'size' => 'One Size',
      'base_price' => 520.0,
      'stock' => 20,
      'in_stock' => true,
      'images' => 
      array (
        0 => '/storage/products/hyperx-cloud-ii.jpg',
      ),
    ),
    1 => 
    array (
      'name' => 'Sony WH-1000XM5',
      'description' => 'Industry-leading noise cancelling wireless headphones with 30-hour battery life.',
      'size' => 'One Size',
      'base_price' => 1099.0,
      'stock' => 15,
      'in_stock' => true,
      'images' => 
      array (
        0 => '/storage/products/sony-wh-1000xm5.jpg',
      ),
    ),
    2 => 
    array (
      'name' => 'Jabra Evolve2 55',
      'description' => 'Professional wireless headset with ANC, certified for Microsoft Teams and Zoom.',
      'size' => 'One Size',
      'base_price' => 850.0,
      'stock' => 10,
      'in_stock' => true,
      'images' => 
      array (
        0 => '/storage/products/jabra-evolve2-55.jpg',
      ),
    ),
    3 => 
    array (
      'name' => 'SteelSeries Arctis 7P+',
      'description' => 'Wireless gaming headset with 30-hour battery and Discord-certified mic.',
      'size' => 'One Size',
      'base_price' => 650.0,
      'stock' => 18,
      'in_stock' => true,
      'images' => 
      array (
        0 => '/storage/products/steelseries-arctis-7p+.jpg',
      ),
    ),
    4 => 
    array (
      'name' => 'Razer BlackShark V2',
      'description' => 'THX 7.1 surround sound gaming headset with Triforce Titanium 50mm drivers.',
      'size' => 'One Size',
      'base_price' => 580.0,
      'stock' => 22,
      'in_stock' => true,
      'images' => 
      array (
        0 => '/storage/products/razer-blackshark-v2.jpg',
      ),
    ),
  ),
  'Monitors' => 
  array (
    0 => 
    array (
      'name' => 'LG UltraWide 34WN80C',
      'description' => '34" curved UltraWide QHD IPS monitor with USB-C connectivity and HDR10 support.',
      'size' => '34 inch',
      'base_price' => 3200.0,
      'stock' => 8,
      'in_stock' => true,
      'images' => 
      array (
        0 => '/storage/products/lg-ultrawide-34wn80c.jpg',
      ),
    ),
    1 => 
    array (
      'name' => 'Samsung Odyssey G5 27',
      'description' => '27" 1440p 165Hz curved gaming monitor with 1ms response time and AMD FreeSync Premium.',
      'size' => '27 inch',
      'base_price' => 2100.0,
      'stock' => 12,
      'in_stock' => true,
      'images' => 
      array (
        0 => '/storage/products/samsung-odyssey-g5-27.jpg',
      ),
    ),
    2 => 
    array (
      'name' => 'Dell UltraSharp U2723QE',
      'description' => '27" 4K USB-C hub monitor with IPS Black panel and Daisy Chain.',
      'size' => '27 inch',
      'base_price' => 2800.0,
      'stock' => 10,
      'in_stock' => true,
      'images' => 
      array (
        0 => '/storage/products/dell-ultrasharp-u2723qe.jpg',
      ),
    ),
    3 => 
    array (
      'name' => 'ASUS ROG Swift PG279QM',
      'description' => '27" 1440p 240Hz G-SYNC gaming monitor with 1ms response.',
      'size' => '27 inch',
      'base_price' => 3500.0,
      'stock' => 6,
      'in_stock' => true,
      'images' => 
      array (
        0 => '/storage/products/asus-rog-swift-pg279qm.jpg',
      ),
    ),
  ),
  'Webcams' => 
  array (
    0 => 
    array (
      'name' => 'Logitech C920 HD Pro',
      'description' => 'Full HD 1080p/30fps webcam with automatic light correction and dual stereo microphones.',
      'size' => 'Standard',
      'base_price' => 350.0,
      'stock' => 45,
      'in_stock' => true,
      'images' => 
      array (
        0 => '/storage/products/logitech-c920-hd-pro.jpg',
      ),
    ),
    1 => 
    array (
      'name' => 'Razer Kiyo Pro',
      'description' => 'Full HD streaming webcam with adaptive light sensor and wide-angle lens.',
      'size' => 'Standard',
      'base_price' => 480.0,
      'stock' => 20,
      'in_stock' => true,
      'images' => 
      array (
        0 => '/storage/products/razer-kiyo-pro.jpg',
      ),
    ),
    2 => 
    array (
      'name' => 'Logitech Brio 4K',
      'description' => '4K Ultra HD webcam with HDR, 90° FOV, and Windows Hello support.',
      'size' => 'Standard',
      'base_price' => 720.0,
      'stock' => 15,
      'in_stock' => true,
      'images' => 
      array (
        0 => '/storage/products/logitech-brio-4k.jpg',
      ),
    ),
    3 => 
    array (
      'name' => 'Elgato Facecam',
      'description' => '1080p60 webcam with Sony STARVIS sensor and fixed focus.',
      'size' => 'Standard',
      'base_price' => 650.0,
      'stock' => 12,
      'in_stock' => true,
      'images' => 
      array (
        0 => '/storage/products/elgato-facecam.jpg',
      ),
    ),
  ),
  'USB Hubs' => 
  array (
    0 => 
    array (
      'name' => 'Anker 10-Port USB 3.0 Hub',
      'description' => '10-port powered USB 3.0 hub with 60W charging port and individual LED switches.',
      'size' => 'Standard',
      'base_price' => 250.0,
      'stock' => 25,
      'in_stock' => true,
      'images' => 
      array (
        0 => '/storage/products/anker-10-port-usb-3.0-hub.jpg',
      ),
    ),
    1 => 
    array (
      'name' => 'Ugreen USB-C Hub 7-in-1',
      'description' => '7-in-1 USB-C hub with 4K HDMI, 100W PD, USB 3.0 x3, SD/TF card reader.',
      'size' => 'Compact',
      'base_price' => 180.0,
      'stock' => 30,
      'in_stock' => true,
      'images' => 
      array (
        0 => '/storage/products/ugreen-usb-c-hub-7-in-1.jpg',
      ),
    ),
    2 => 
    array (
      'name' => 'CalDigit TS4',
      'description' => '18-port Thunderbolt 4 dock with 98W charging and 2.5GbE.',
      'size' => 'Standard',
      'base_price' => 1450.0,
      'stock' => 8,
      'in_stock' => true,
      'images' => 
      array (
        0 => '/storage/products/caldigit-ts4.jpg',
      ),
    ),
    3 => 
    array (
      'name' => 'Anker 568 USB-C Dock',
      'description' => '11-in-1 USB-C dock with 4K HDMI, 100W PD, and Ethernet.',
      'size' => 'Compact',
      'base_price' => 850.0,
      'stock' => 12,
      'in_stock' => true,
      'images' => 
      array (
        0 => '/storage/products/anker-568-usb-c-dock.jpg',
      ),
    ),
  ),
  'Cables & Adapters' => 
  array (
    0 => 
    array (
      'name' => 'Ugreen USB-C to HDMI 2.1 Cable',
      'description' => '2m USB-C to HDMI 2.1 cable supporting 8K/60Hz and 4K/144Hz output.',
      'size' => '2m',
      'base_price' => 95.0,
      'stock' => 50,
      'in_stock' => true,
      'images' => 
      array (
        0 => '/storage/products/ugreen-usb-c-to-hdmi-2.1-cable.jpg',
      ),
    ),
    1 => 
    array (
      'name' => 'Baseus 100W GaN Charger',
      'description' => 'Compact 100W GaN fast charger with 2x USB-C + 1x USB-A ports.',
      'size' => 'Compact',
      'base_price' => 220.0,
      'stock' => 40,
      'in_stock' => true,
      'images' => 
      array (
        0 => '/storage/products/baseus-100w-gan-charger.jpg',
      ),
    ),
    2 => 
    array (
      'name' => 'Anker PowerLine III USB-C to USB-C 2.0',
      'description' => '2m 100W USB-C to USB-C cable with nylon braiding.',
      'size' => '2m',
      'base_price' => 80.0,
      'stock' => 60,
      'in_stock' => true,
      'images' => 
      array (
        0 => '/storage/products/anker-powerline-iii-usb-c-to-usb-c-2.0.jpg',
      ),
    ),
    3 => 
    array (
      'name' => 'Belkin Thunderbolt 3 Cable',
      'description' => '0.8m Thunderbolt 3 40Gbps cable with 100W charging.',
      'size' => '0.8m',
      'base_price' => 180.0,
      'stock' => 20,
      'in_stock' => true,
      'images' => 
      array (
        0 => '/storage/products/belkin-thunderbolt-3-cable.jpg',
      ),
    ),
  ),
  'Storage' => 
  array (
    0 => 
    array (
      'name' => 'Samsung 990 PRO 2TB',
      'description' => 'PCIe 4.0 NVMe M.2 SSD with 7450/6900 MB/s read/write.',
      'size' => '2TB',
      'base_price' => 850.0,
      'stock' => 15,
      'in_stock' => true,
      'images' => 
      array (
        0 => '/storage/products/samsung-990-pro-2tb.jpg',
      ),
    ),
    1 => 
    array (
      'name' => 'WD Black SN850X 2TB',
      'description' => 'PCIe 4.0 NVMe gaming SSD with 7300 MB/s read speed.',
      'size' => '2TB',
      'base_price' => 820.0,
      'stock' => 18,
      'in_stock' => true,
      'images' => 
      array (
        0 => '/storage/products/wd-black-sn850x-2tb.jpg',
      ),
    ),
    2 => 
    array (
      'name' => 'SanDisk Extreme Portable SSD 2TB',
      'description' => 'USB 3.2 Gen 2x2 portable SSD with 2000 MB/s speed.',
      'size' => '2TB',
      'base_price' => 950.0,
      'stock' => 12,
      'in_stock' => true,
      'images' => 
      array (
        0 => '/storage/products/sandisk-extreme-portable-ssd-2tb.jpg',
      ),
    ),
    3 => 
    array (
      'name' => 'Seagate FireCuda 530 2TB',
      'description' => 'PCIe 4.0 NVMe SSD with heatsink for PS5 and PC.',
      'size' => '2TB',
      'base_price' => 900.0,
      'stock' => 10,
      'in_stock' => true,
      'images' => 
      array (
        0 => '/storage/products/seagate-firecuda-530-2tb.jpg',
      ),
    ),
  ),
  'Audio' => 
  array (
    0 => 
    array (
      'name' => 'Audio-Technica AT2020',
      'description' => 'Cardioid condenser microphone for studio recording.',
      'size' => 'Standard',
      'base_price' => 420.0,
      'stock' => 15,
      'in_stock' => true,
      'images' => 
      array (
        0 => '/storage/products/audio-technica-at2020.jpg',
      ),
    ),
    1 => 
    array (
      'name' => 'Blue Yeti USB Mic',
      'description' => 'Multi-pattern USB microphone for streaming and podcasting.',
      'size' => 'Standard',
      'base_price' => 550.0,
      'stock' => 20,
      'in_stock' => true,
      'images' => 
      array (
        0 => '/storage/products/blue-yeti-usb-mic.jpg',
      ),
    ),
    2 => 
    array (
      'name' => 'Elgato Wave:3',
      'description' => 'Premium USB condenser mic with Clipguard technology.',
      'size' => 'Standard',
      'base_price' => 720.0,
      'stock' => 10,
      'in_stock' => true,
      'images' => 
      array (
        0 => '/storage/products/elgato-wave-3.jpg',
      ),
    ),
    3 => 
    array (
      'name' => 'Rode NT-USB+',
      'description' => 'Professional USB microphone with on-mic controls.',
      'size' => 'Standard',
      'base_price' => 780.0,
      'stock' => 8,
      'in_stock' => true,
      'images' => 
      array (
        0 => '/storage/products/rode-nt-usb+.jpg',
      ),
    ),
  ),
  'Lighting' => 
  array (
    0 => 
    array (
      'name' => 'Elgato Key Light Air',
      'description' => 'Compact panel light with 1400 lumens and app control.',
      'size' => 'Compact',
      'base_price' => 650.0,
      'stock' => 12,
      'in_stock' => true,
      'images' => 
      array (
        0 => '/storage/products/elgato-key-light-air.jpg',
      ),
    ),
    1 => 
    array (
      'name' => 'Razer Key Light Chroma',
      'description' => 'RGB streaming light with 2800 lumens and Razer Chroma sync.',
      'size' => 'Standard',
      'base_price' => 850.0,
      'stock' => 8,
      'in_stock' => true,
      'images' => 
      array (
        0 => '/storage/products/razer-key-light-chroma.jpg',
      ),
    ),
    2 => 
    array (
      'name' => 'Logitech Litra Glow',
      'description' => 'Premium streaming light with TrueSoft technology and 250 lumens.',
      'size' => 'Compact',
      'base_price' => 450.0,
      'stock' => 15,
      'in_stock' => true,
      'images' => 
      array (
        0 => '/storage/products/logitech-litra-glow.jpg',
      ),
    ),
  ),
);

        foreach ($products as $categoryName => $items) {
            $this->command->info("Processing category: {$categoryName}");
            $category = Category::where("name", $categoryName)->firstOrFail();

            foreach ($items as $item) {
                Product::firstOrCreate(
                    ["name" => $item["name"]],
                    ["category_id" => $category->id] + $item
                );
            }
        }
        $this->command->info("ProductSeeder completed. Total products: " . Product::count() . ".");
    }
}