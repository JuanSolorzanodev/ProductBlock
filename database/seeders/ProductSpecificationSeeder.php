<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class ProductSpecificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('product_specifications')->insert([
            [
                'product_id' => 1,
                'packaging_type' => 'Box',
                'material' => 'Plastic',
                'usage_location' => 'Indoor',
                'color' => '#ff0000',
                'load_capacity' => '20kg',
                'country_of_origin' => 'USA',
                'warranty' => true,
                'number_of_pieces' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'product_id' => 2,
                'packaging_type' => 'Bag',
                'material' => 'Metal',
                'usage_location' => 'Outdoor',
                'color' => '#000000',
                'load_capacity' => '50kg',
                'country_of_origin' => 'Germany',
                'warranty' => false,
                'number_of_pieces' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'product_id' => 3,
                'packaging_type' => 'small',
                'material' => 'Madera',
                'usage_location' => 'Outdoor',
                'color' => '#0056b3',
                'load_capacity' => '30kg',
                'country_of_origin' => 'ecuador',
                'warranty' => false,
                'number_of_pieces' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Agrega más datos si es necesario
        ]);
    }
}
