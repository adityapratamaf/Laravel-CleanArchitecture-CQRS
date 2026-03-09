<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('products')->insert([
            [
                'name' => 'Product A',
                'sku' => 'SKU-A-001',
                'price' => 125000.00,
                'stock' => 20,
                'description' => 'Sample product A',
                'image' => 'https://via.placeholder.com/640x480?text=Product+A',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Product B',
                'sku' => 'SKU-B-001',
                'price' => 99000.00,
                'stock' => 10,
                'description' => 'Sample product B',
                'image' => 'https://via.placeholder.com/640x480?text=Product+B',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}