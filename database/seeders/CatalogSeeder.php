<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        // =========================================
        // CATEGORIES
        // =========================================
        $categories = [
            [
                'name' => 'Custom Keyboards',
                'slug' => 'custom-keyboards',
                'description' => 'Koleksi keyboard custom premium berbagai layout.',
                'icon' => '/icons/keyboard.svg',
            ],
            [
                'name' => 'Mechanical Switches',
                'slug' => 'mechanical-switches',
                'description' => 'Beragam switch linear, tactile, dan clicky.',
                'icon' => '/icons/switch.svg',
            ],
            [
                'name' => 'Keycaps Sets',
                'slug' => 'keycaps-sets',
                'description' => 'Kumpulan keycaps berbagai profil, warna, dan material.',
                'icon' => '/icons/keycaps.svg',
            ],
        ];

        $categoryModels = [];

        foreach ($categories as $data) {
            $categoryModels[$data['slug']] = Category::firstOrCreate(
                ['slug' => $data['slug']],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'icon' => $data['icon'],
                    'is_active' => true,
                ],
            );
        }

        // =========================================
        // PRODUCTS (KEYBOARDS + SWITCHES + KEYCAPS)
        // =========================================
        $products = [

            // ---------------------------------------
            // KEYBOARDS
            // ---------------------------------------
            [
                'category_slug' => 'custom-keyboards',
                'name' => 'Dusky Peach 65 Wireless',
                'slug' => 'dusky-peach-65-wireless',
                'short_description' => 'Keyboard 65% bertema peach & black.',
                'description' => 'Keyboard minimalis 65% dengan kombinasi warna peach-black dan kabel coiled premium.',
                'price' => 1399000,
                'compare_at_price' => 1599000,
                'status' => 'active',
                'badge' => 'Aesthetic Set',
                'thumbnail' => '/storage/products/keyboard1.jpg',
                'variants' => [
                    ['name' => 'Silent Linear', 'stock' => 15],
                    ['name' => 'Tactile Brown', 'price' => 1449000, 'compare_at_price' => 1699000, 'stock' => 12],
                ],
            ],

            [
                'category_slug' => 'custom-keyboards',
                'name' => 'Navy Frost 75 Compact',
                'slug' => 'navy-frost-75-compact',
                'short_description' => 'Keyboard 75% navy-lilac.',
                'description' => 'Desain modern 75% dengan warna navy dan aksen lilac cocok untuk gaming.',
                'price' => 1699000,
                'compare_at_price' => 1899000,
                'status' => 'active',
                'badge' => 'Best Seller',
                'thumbnail' => '/storage/products/keyboard2.jpg',
                'variants' => [
                    ['name' => 'Linear Speed', 'stock' => 10],
                ],
            ],

            [
                'category_slug' => 'custom-keyboards',
                'name' => 'Royal Blue 75 Pro',
                'slug' => 'royal-blue-75-pro',
                'short_description' => 'Keyboard 75% biru royal.',
                'description' => 'Keyboard dengan estetika biru-putih elegan.',
                'price' => 1899000,
                'compare_at_price' => 2199000,
                'status' => 'active',
                'badge' => 'Premium',
                'thumbnail' => '/storage/products/keyboard3.jpg',
                'variants' => [
                    ['name' => 'Gateron Linear', 'stock' => 14],
                ],
            ],

            [
                'category_slug' => 'custom-keyboards',
                'name' => 'Eggy Mecha TKL',
                'slug' => 'eggy-mecha-tkl',
                'short_description' => 'TKL playful bertema telur.',
                'description' => 'Keycaps unik dengan tema telur & aksen kuning.',
                'price' => 1599000,
                'compare_at_price' => 1799000,
                'status' => 'active',
                'badge' => 'Unique Pick',
                'thumbnail' => '/storage/products/keyboard4.jpg',
                'variants' => [
                    ['name' => 'Tactile Orange', 'stock' => 20],
                ],
            ],

            [
                'category_slug' => 'custom-keyboards',
                'name' => 'Hellenic Blue TKL',
                'slug' => 'hellenic-blue-tkl',
                'short_description' => 'TKL dengan motif Yunani.',
                'description' => 'Tema biru–putih klasik dengan motif Greek Key premium.',
                'price' => 1999000,
                'compare_at_price' => 2399000,
                'status' => 'active',
                'badge' => 'Studio Edition',
                'thumbnail' => '/storage/products/keyboard5.jpg',
                'variants' => [
                    ['name' => 'Holy Panda Clone', 'price' => 2099000, 'compare_at_price' => 2499000, 'stock' => 9],
                ],
            ],

            // ---------------------------------------
            // SWITCHES
            // ---------------------------------------
            [
                'category_slug' => 'mechanical-switches',
                'name' => 'Peach Tactile V3 Switch',
                'slug' => 'peach-tactile-v3',
                'short_description' => 'Tactile feel dengan warna peach.',
                'description' => 'Switch tactile ringan dengan housing transparan peach.',
                'price' => 4500,
                'compare_at_price' => 5500,
                'status' => 'active',
                'badge' => 'Popular',
                'thumbnail' => '/storage/products/switch1.jpg',
                'variants' => [
                    ['name' => 'Pack 35 pcs', 'stock' => 40],
                    ['name' => 'Pack 70 pcs', 'stock' => 20],
                ],
            ],

            [
                'category_slug' => 'mechanical-switches',
                'name' => 'Mint Silent Linear',
                'slug' => 'mint-silent-linear',
                'short_description' => 'Switch linear senyap.',
                'description' => 'Silent linear dengan housing mint untuk typing yang halus.',
                'price' => 5200,
                'compare_at_price' => 6000,
                'status' => 'active',
                'badge' => 'Silent',
                'thumbnail' => '/storage/products/switch2.jpg',
                'variants' => [
                    ['name' => 'Pack 36 pcs', 'stock' => 30],
                ],
            ],

            [
                'category_slug' => 'mechanical-switches',
                'name' => 'Arctic Blue Linear',
                'slug' => 'arctic-blue-linear',
                'short_description' => 'Switch linear biru es.',
                'description' => 'Linear switch smooth dengan housing biru transparan.',
                'price' => 4800,
                'status' => 'active',
                'badge' => 'New',
                'thumbnail' => '/storage/products/switch3.jpg',
                'variants' => [
                    ['name' => 'Pack 36 pcs', 'stock' => 25],
                ],
            ],

            [
                'category_slug' => 'mechanical-switches',
                'name' => 'Sunny Yellow Box Switch',
                'slug' => 'sunny-yellow-box',
                'short_description' => 'Switch kuning cerah.',
                'description' => 'Switch stabil dengan desain box switch responsif.',
                'price' => 4000,
                'compare_at_price' => 5000,
                'status' => 'active',
                'badge' => 'Budget Pick',
                'thumbnail' => '/storage/products/switch4.jpg',
                'variants' => [
                    ['name' => 'Pack 35 pcs', 'stock' => 50],
                ],
            ],

            // ---------------------------------------
            // KEYCAPS SETS
            // ---------------------------------------
            [
                'category_slug' => 'keycaps-sets',
                'name' => 'Macaroon Inner Purple 126 Keys',
                'slug' => 'macaroon-inner-purple-126',
                'short_description' => 'Keycaps pastel PBT.',
                'description' => 'PBT 126 keys dengan warna pastel ungu-pink.',
                'price' => 249000,
                'compare_at_price' => 299000,
                'status' => 'active',
                'badge' => 'New Arrival',
                'thumbnail' => '/storage/products/keycaps-macaroon-purple.jpg',
                'variants' => [
                    ['name' => '126 Keys Set', 'stock' => 35],
                ],
            ],

            [
                'category_slug' => 'keycaps-sets',
                'name' => 'Purple Haze Gradient 133 Keys',
                'slug' => 'purple-haze-gradient-133',
                'short_description' => 'Keycaps gradient ungu.',
                'description' => 'OEM profile dengan side printing dan gradasi ungu gelap–terang.',
                'price' => 279000,
                'compare_at_price' => 329000,
                'status' => 'active',
                'badge' => 'Gradient Series',
                'thumbnail' => '/storage/products/keycaps-purple-haze.jpg',
                'variants' => [
                    ['name' => '133 Keys Set', 'stock' => 28],
                ],
            ],

            [
                'category_slug' => 'keycaps-sets',
                'name' => 'Lucky Red Gradient 133 Keys',
                'slug' => 'lucky-red-gradient-133',
                'short_description' => 'Keycaps gradasi merah.',
                'description' => 'Keycaps OEM merah dengan efek gradient elegan.',
                'price' => 279000,
                'compare_at_price' => 329000,
                'status' => 'active',
                'badge' => 'Hot Colorway',
                'thumbnail' => '/storage/products/keycaps-lucky-red.jpg',
                'variants' => [
                    ['name' => '133 Keys Set', 'stock' => 30],
                ],
            ],

            [
                'category_slug' => 'keycaps-sets',
                'name' => 'Blue Attack Mech 126 Keys',
                'slug' => 'blue-attack-mech-126',
                'short_description' => 'Keycaps tri color.',
                'description' => 'Desain biru-merah-putih dengan material PBT injection molding.',
                'price' => 259000,
                'compare_at_price' => 309000,
                'status' => 'active',
                'badge' => 'Tri-Color',
                'thumbnail' => '/storage/products/keycaps-blue-attack.jpg',
                'variants' => [
                    ['name' => '126 Keys Set', 'stock' => 40],
                ],
            ],

            [
                'category_slug' => 'keycaps-sets',
                'name' => 'Polar Day Gradient 133 Keys',
                'slug' => 'polar-day-gradient-133',
                'short_description' => 'Keycaps gradient hitam–putih.',
                'description' => 'Keycaps OEM dengan efek gradient clean minimalis.',
                'price' => 279000,
                'compare_at_price' => 329000,
                'status' => 'active',
                'badge' => 'Minimalist',
                'thumbnail' => '/storage/products/keycaps-polar-day.jpg',
                'variants' => [
                    ['name' => '133 Keys Set', 'stock' => 22],
                ],
            ],

        ];

        // =========================================
        // INSERT PRODUCTS
        // =========================================
        foreach ($products as $data) {
            $category = $categoryModels[$data['category_slug']];

            $product = Product::firstOrCreate(
                ['slug' => $data['slug']],
                [
                    'category_id' => $category->id,
                    'name' => $data['name'],
                    'short_description' => $data['short_description'],
                    'description' => $data['description'],
                    'price' => $data['price'],
                    'compare_at_price' => $data['compare_at_price'] ?? null,
                    'stock' => 0,
                    'status' => $data['status'],
                    'badge' => $data['badge'],
                    'thumbnail' => $data['thumbnail'],
                ],
            );

            foreach ($data['variants'] as $variant) {
                ProductVariant::firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'name' => $variant['name'],
                    ],
                    [
                        'price' => $variant['price'] ?? null,
                        'compare_at_price' => $variant['compare_at_price'] ?? null,
                        'stock' => $variant['stock'],
                    ],
                );
            }

            // Update total stock
            $product->update([
                'stock' => $product->variants()->sum('stock'),
            ]);
        }
    }
}
