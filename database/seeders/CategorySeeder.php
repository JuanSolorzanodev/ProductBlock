<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['name' => 'Cementos'],
            ['name' => 'Pinturas'],
            ['name' => 'Herramientas'],
            ['name' => 'Maderas'],
            ['name' => 'Metales'],
        ];

        DB::table('categories')->insert($categories);
    }
}
