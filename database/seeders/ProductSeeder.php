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
                'stock' => 0,
                'SKU' => 'CP1001',
                'iva' => true,
                'category_id' => 1,
                'images' => [
                    ['image_path' => 'http://localhost:8000/storage/products/7lcBgippr9NNTeP5U1c6BSPVLn9xQIMywabnw4kO.jpg', 'top' => 1],
                    ['image_path' => 'http://localhost:8000/storage/products/tAMcUUong0Nd358bnPXCNlFHy3khQZ4zkCGwVxPN.png', 'top' => 2],
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
                    ['image_path' => 'http://localhost:8000/storage/products/fyP85ORtYKizYa98o6MjzewJC2zZkjBSXaB9Rm0K.jpg', 'top' => 1],
                    ['image_path' => 'http://localhost:8000/storage/products/SwlFKViYVura8bxT8e4QKSHZVZJYziyWMf8WKceG.png', 'top' => 2],
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
                    ['image_path' => 'http://localhost:8000/storage/products/KqLSbGXxzAyxcrGhEJy0XG2VGerh6SL7dF9DYpUL.jpg', 'top' => 1],
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
