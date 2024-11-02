<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductImage;

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
                'SKU' => 'CP1001',
                'iva' => true,
                'category_id' => 1,
                'images' => [
                    ['image_path' => 'images/cemento_portland_1.jpg', 'top' => 1],
                    ['image_path' => 'images/cemento_portland_2.jpg', 'top' => 2],
                ]
            ],
            [
                'name' => 'Pintura blanca mate',
                'description' => 'Pintura ideal para interiores y exteriores.',
                'price' => 85.50,
                'stock' => 100,
                'SKU' => 'PB1002',
                'iva' => true,
                'category_id' => 2,
                'images' => [
                    ['image_path' => 'images/pintura_blanca_mate_1.jpg', 'top' => 1],
                    ['image_path' => 'images/pintura_blanca_mate_2.jpg', 'top' => 2],
                ]
            ],
            [
                'name' => 'Martillo de carpintero',
                'description' => 'Martillo resistente con mango de madera.',
                'price' => 25.00,
                'stock' => 200,
                'SKU' => 'MC1003',
                'iva' => false,
                'category_id' => 3,
                'images' => [
                    ['image_path' => 'images/martillo_carpintero_1.jpg', 'top' => 1],
                ]
            ],
            // Añade más productos según necesites
        ];

        foreach ($products as $productData) {
            // Extrae las imágenes antes de crear el producto
            $images = $productData['images'];
            unset($productData['images']);

            // Crea el producto
            $product = Product::create($productData);

            // Crea las imágenes asociadas al producto
            foreach ($images as $imageData) {
                $imageData['product_id'] = $product->id; // Asocia la imagen al producto
                ProductImage::create($imageData);
            }
        }
    }
}
