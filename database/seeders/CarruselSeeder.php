<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class CarruselSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('carrusel_images')->insert([
            [
                'image_path' => 'https://res.cloudinary.com/dinpmwqyi/image/upload/v1738179365/carousel/eat8bw1z4bux8jmiil7x.jpg',
                'name' => 'fondo_azul_panoramico.jpg',
                'size' => '5000',
                'top' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            /* [
                'image_path' => 'https://res.cloudinary.com/dinpmwqyi/image/upload/v1738188082/carousel/r8cq4adsfbkpnmv1tdxh.jpg',
                'name' => 'minita_azul_panoramico.jpg',
                'size' => '4000',
                'top' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ] */
            // Agrega más imágenes aquí si lo deseas
        ]);
    }
}
