<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Basic categories
        $categories = [
            [
                'name' => '75% & TKL',
                'slug' => '75-tkl',
                'description' => 'Keyboard layout 75% dan TKL untuk produktivitas dan gaming.',
                'icon' => '/icons/tkl.svg',
            ],
            [
                'name' => '65% & 60%',
                'slug' => '65-60',
                'description' => 'Form factor kompak 65% & 60% untuk meja minimalis.',
                'icon' => '/icons/60.svg',
            ],
            [
                'name' => 'Barebone Kits',
                'slug' => 'barebone-kits',
                'description' => 'Base keyboard tanpa switch & keycaps untuk custom setup.',
                'icon' => '/icons/barebone.svg',
            ],
            [
                'name' => 'Accessories',
                'slug' => 'accessories',
                'description' => 'Wrist rest, kabel, dan aksesoris pendukung setup.',
                'icon' => '/icons/accessories.svg',
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

        // Sample products & variants
        $products = [
            [
                'category_slug' => '75-tkl',
                'name' => 'LKBoard K81 Wireless',
                'slug' => 'lkboard-k81-wireless',
                'short_description' => '75% wireless tri-mode dengan gasket mount.',
                'description' => 'LKBoard K81 Wireless hadir dengan layout 75%, gasket mount, dan konektivitas tri-mode (2.4G, BT, kabel). Cocok untuk kerja dan gaming.',
                'price' => 1599000,
                'compare_at_price' => 1899000,
                'status' => 'active',
                'badge' => '11.11 Deal',
                'thumbnail' => null,
                'variants' => [
                    [
                        'name' => 'Gateron G Pro Brown',
                        'price' => null,
                        'compare_at_price' => null,
                        'stock' => 24,
                    ],
                    [
                        'name' => 'Gateron G Pro Yellow',
                        'price' => 1649000,
                        'compare_at_price' => 1949000,
                        'stock' => 12,
                    ],
                ],
            ],
            [
                'category_slug' => '75-tkl',
                'name' => 'LKBoard 75 Pro Frost',
                'slug' => 'lkboard-75-pro-frost',
                'short_description' => 'Keyboard 75% premium dengan plate alu.',
                'description' => 'Seri Frost membawa plate aluminium dan peredam multi-layer untuk sound profile clean.',
                'price' => 2199000,
                'compare_at_price' => 2599000,
                'status' => 'active',
                'badge' => 'Hot',
                'thumbnail' => null,
                'variants' => [
                    [
                        'name' => 'Kailh Box Cream',
                        'price' => null,
                        'compare_at_price' => null,
                        'stock' => 10,
                    ],
                    [
                        'name' => 'Wuque Studio Tactile',
                        'price' => 2299000,
                        'compare_at_price' => 2699000,
                        'stock' => 8,
                    ],
                ],
            ],
            [
                'category_slug' => '65-60',
                'name' => 'LKBoard 65 Pro Wireless',
                'slug' => 'lkboard-65-pro-wireless',
                'short_description' => '65% gasket mount tri-mode.',
                'description' => '65% keyboard dengan knob multifungsi dan koneksi tri-mode.',
                'price' => 1899000,
                'compare_at_price' => 2099000,
                'status' => 'active',
                'badge' => 'Work setup',
                'thumbnail' => null,
                'variants' => [
                    [
                        'name' => 'LK Silent Tactile',
                        'price' => null,
                        'compare_at_price' => null,
                        'stock' => 20,
                    ],
                    [
                        'name' => 'Gateron Oil King',
                        'price' => 1999000,
                        'compare_at_price' => 2299000,
                        'stock' => 15,
                    ],
                ],
            ],
            [
                'category_slug' => '65-60',
                'name' => 'LKBoard 60 Minimal Wired',
                'slug' => 'lkboard-60-minimal-wired',
                'short_description' => '60% layout minimalis dengan koneksi kabel.',
                'description' => 'Keyboard 60% untuk setup minimalis, cocok untuk programmer dan penulis yang ingin meja super bersih.',
                'price' => 999000,
                'compare_at_price' => null,
                'status' => 'active',
                'badge' => 'Entry pick',
                'thumbnail' => null,
                'variants' => [
                    [
                        'name' => 'LK Silent Red',
                        'price' => null,
                        'compare_at_price' => null,
                        'stock' => 35,
                    ],
                    [
                        'name' => 'LK Silent Linear',
                        'price' => 1049000,
                        'compare_at_price' => null,
                        'stock' => 25,
                    ],
                ],
            ],
            [
                'category_slug' => 'barebone-kits',
                'name' => 'LKBoard Barebone 65 Kit',
                'slug' => 'lkboard-barebone-65-kit',
                'short_description' => 'Barebone kit 65% siap mod.',
                'description' => 'Barebone kit 65% untuk kamu yang ingin build custom keyboard dari nol.',
                'price' => 1399000,
                'compare_at_price' => 1599000,
                'status' => 'active',
                'badge' => 'Barebone',
                'thumbnail' => null,
                'variants' => [
                    [
                        'name' => 'Frost White Case',
                        'price' => null,
                        'compare_at_price' => null,
                        'stock' => 18,
                    ],
                    [
                        'name' => 'Smoke Black Case',
                        'price' => null,
                        'compare_at_price' => null,
                        'stock' => 12,
                    ],
                ],
            ],
            [
                'category_slug' => 'barebone-kits',
                'name' => 'LKBoard Barebone 75 Kit',
                'slug' => 'lkboard-barebone-75-kit',
                'short_description' => 'Barebone kit 75% dengan knob.',
                'description' => 'Varian barebone 75% dengan knob aluminium dan gasket mount.',
                'price' => 1699000,
                'compare_at_price' => 1899000,
                'status' => 'active',
                'badge' => 'Studio Series',
                'thumbnail' => null,
                'variants' => [
                    [
                        'name' => 'Silver Case',
                        'price' => null,
                        'compare_at_price' => null,
                        'stock' => 10,
                    ],
                    [
                        'name' => 'Gunmetal Case',
                        'price' => 1749000,
                        'compare_at_price' => 1999000,
                        'stock' => 9,
                    ],
                ],
            ],
            [
                'category_slug' => 'accessories',
                'name' => 'LK Wrist Rest Artisan',
                'slug' => 'lk-wrist-rest-artisan',
                'short_description' => 'Wrist rest kayu solid.',
                'description' => 'Wrist rest artisan dibuat dari kayu walnut dengan finishing natural oil.',
                'price' => 399000,
                'compare_at_price' => 459000,
                'status' => 'active',
                'badge' => 'New',
                'thumbnail' => null,
                'variants' => [
                    [
                        'name' => 'Size 65%',
                        'price' => null,
                        'compare_at_price' => null,
                        'stock' => 30,
                    ],
                    [
                        'name' => 'Size 75%',
                        'price' => 429000,
                        'compare_at_price' => 489000,
                        'stock' => 22,
                    ],
                ],
            ],
            [
                'category_slug' => 'accessories',
                'name' => 'LK Coiled Cable Paracord',
                'slug' => 'lk-coiled-cable-paracord',
                'short_description' => 'Kabel USB-C coiled premium.',
                'description' => 'Coiled cable dengan konektor aviator dan paracord premium, cocok untuk setup custom.',
                'price' => 299000,
                'compare_at_price' => 349000,
                'status' => 'active',
                'badge' => 'Bundle Deal',
                'thumbnail' => null,
                'variants' => [
                    [
                        'name' => 'Midnight Blue',
                        'price' => null,
                        'compare_at_price' => null,
                        'stock' => 40,
                    ],
                    [
                        'name' => 'Sunset Orange',
                        'price' => null,
                        'compare_at_price' => null,
                        'stock' => 35,
                    ],
                ],
            ],
        ];

        foreach ($products as $data) {
            $category = $categoryModels[$data['category_slug']] ?? null;

            if (! $category) {
                continue;
            }

            $product = Product::firstOrCreate(
                ['slug' => $data['slug']],
                [
                    'category_id' => $category->id,
                    'name' => $data['name'],
                    'short_description' => $data['short_description'],
                    'description' => $data['description'],
                    'price' => $data['price'],
                    'compare_at_price' => $data['compare_at_price'],
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
                        'price' => $variant['price'],
                        'compare_at_price' => $variant['compare_at_price'],
                        'stock' => $variant['stock'],
                    ],
                );
            }

            // Optional: sync product stock as sum of variant stock
            $product->update([
                'stock' => $product->variants()->sum('stock'),
            ]);
        }
    }
}
