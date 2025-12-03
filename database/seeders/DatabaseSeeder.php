<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Kategori, produk, dan varian dummy
        $this->call(CatalogSeeder::class);
    }
}
