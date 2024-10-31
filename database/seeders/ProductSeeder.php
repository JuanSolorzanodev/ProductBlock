<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $products = [
            [
                'name' => 'Cemento Portland',
                'description' => 'Cemento de alta resistencia para todo tipo de construcción.',
                'price' => 120.00,
                'stock' => 50,
                'category_id' => 1, // Asegúrate que coincida con el ID en categories
            ],
            [
                'name' => 'Pintura blanca mate',
                'description' => 'Pintura ideal para interiores y exteriores.',
                'price' => 85.50,
                'stock' => 100,
                'category_id' => 2,
            ],
            [
                'name' => 'Martillo de carpintero',
                'description' => 'Martillo resistente con mango de madera.',
                'price' => 25.00,
                'stock' => 200,
                'category_id' => 3,
            ],
            // Añade más productos según necesites
        ];

        DB::table('products')->insert($products);
    }
}
