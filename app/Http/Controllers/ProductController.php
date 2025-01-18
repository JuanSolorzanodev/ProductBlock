<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\ProductImage;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;


class ProductController extends Controller
{

    public function index(Request $request)
    {
        $query = $request->input('name');

        // Obtiene todos los productos con sus imágenes, filtrando por nombre si se proporciona
        $products = Product::with('images')
            ->when($query, function ($queryBuilder) use ($query) {
                return $queryBuilder->where('name', 'like', '%' . $query . '%');
            })
            ->get();

        return response()->json(['data' => $products], 200);
    }

    public function store(Request $request)
    {
        // Validar los datos del producto y las especificaciones
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'SKU' => 'required|string|max:255|unique:products,SKU',
            'iva' => 'required|boolean',
            'category_id' => 'required|exists:categories,id',
            'packaging_type' => 'nullable|string|max:255',
            'material' => 'nullable|string|max:255',
            'usage_location' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
            'load_capacity' => 'nullable|string|max:255',
            'country_of_origin' => 'nullable|string|max:255',
            'warranty' => 'nullable|boolean',
            'number_of_pieces' => 'nullable|integer|min:1',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Validar imágenes
        ]);

        // Crear el producto
        $product = Product::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'price' => $request->input('price'),
            'stock' => $request->input('stock'),
            'SKU' => $request->input('SKU'),
            'iva' => $request->input('iva'),
            'category_id' => $request->input('category_id'),
        ]);

        // Crear las especificaciones del producto
        $product->specifications()->create([
            'packaging_type' => $request->input('packaging_type'),
            'material' => $request->input('material'),
            'usage_location' => $request->input('usage_location'),
            'color' => $request->input('color'),
            'load_capacity' => $request->input('load_capacity'),
            'country_of_origin' => $request->input('country_of_origin'),
            'warranty' => $request->input('warranty', false),
            'number_of_pieces' => $request->input('number_of_pieces', 1),
        ]);

        // Subir imágenes (máximo 3)
        if ($request->hasFile('images')) {
            $images = $request->file('images');
            foreach ($images as $key => $image) {
                if ($key > 2) break; // Limitar a 3 imágenes

                $path = $image->store('products', 'public');
                $product->images()->create([
                    'image_path' => 'http://localhost:8000/storage/' . $path, // Agrega automáticamente el prefijo
                    'top' => $key + 1,
                ]);
            }
        }

        // Responder con el producto creado
        return response()->json([
            'message' => 'Producto creado con éxito',
            'data' => $product->load('specifications', 'images'),
        ], 201);
    }

    public function handleImages($product, $images)
    {
        // Eliminar imágenes existentes
        foreach ($product->images as $image) {
            Storage::disk('public')->delete(str_replace('storage/', '', $image->image_path));
            $image->delete();
        }

        // Subir nuevas imágenes (máximo 3)
        foreach ($images as $key => $image) {
            if ($key > 2) break; // Limitar a 3 imágenes

            $path = $image->store('products', 'public');
            $product->images()->create([
                'image_path' => 'http://localhost:8000/storage/' . $path, // Agregar automáticamente el prefijo
                'top' => $key + 1,
            ]);
        }
    }



    public function update(Request $request, $id)
    {
        // Validar los datos del producto y las especificaciones
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'SKU' => 'required|string|max:255|unique:products,SKU,' . $id,
            'iva' => 'required|boolean',
            'category_id' => 'required|exists:categories,id',
            'packaging_type' => 'nullable|string|max:255',
            'material' => 'nullable|string|max:255',
            'usage_location' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
            'load_capacity' => 'nullable|string|max:255',
            'country_of_origin' => 'nullable|string|max:255',
            'warranty' => 'nullable|boolean',
            'number_of_pieces' => 'nullable|integer|min:1',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Encontrar el producto
        $product = Product::findOrFail($id);

        // Actualizar datos del producto
        $product->update($request->only([
            'name',
            'description',
            'price',
            'stock',
            'SKU',
            'iva',
            'category_id'
        ]));

        // Actualizar especificaciones
        $product->specifications->update($request->only([
            'packaging_type',
            'material',
            'usage_location',
            'color',
            'load_capacity',
            'country_of_origin',
            'warranty',
            'number_of_pieces'
        ]));

        // Manejar imágenes si se envían
        if ($request->hasFile('images')) {
            $this->handleImages($product, $request->file('images'));
        }

        // Responder con el producto actualizado
        return response()->json([
            'message' => 'Producto actualizado con éxito',
            'data' => $product->load('specifications', 'images'),
        ], 200);
    }


    public function updateImages(Request $request, $id)
    {
        // Validar imágenes
        $request->validate([
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Encontrar el producto
        $product = Product::findOrFail($id);

        // Manejar las imágenes
        $this->handleImages($product, $request->file('images'));

        return response()->json([
            'message' => 'Imágenes actualizadas con éxito',
            'data' => $product->images,
        ], 200);
    }


    public function destroy($id)
    {
        // Encontrar el producto
        $product = Product::findOrFail($id);
    
        // Eliminar las especificaciones y las imágenes de forma lógica
        $product->specifications()->delete();
        $product->images()->delete();
    
        // Eliminar el producto de forma lógica
        $product->delete();
    
        return response()->json([
            'message' => 'Producto eliminado de forma lógica, incluyendo especificaciones e imágenes relacionadas',
            'data' => $product,
        ], 200);
    }
    
    public function cleanUnusedImages()
    {
        // Obtén las rutas de las imágenes almacenadas en la tabla product_images
        $storedImages = DB::table('product_images')->pluck('image_path')->toArray();

        // Extrae solo los nombres de los archivos desde las rutas
        $storedImageNames = array_map(function ($path) {
            return basename($path);
        }, $storedImages);
    // Obtén todas las imágenes existentes en el directorio storage/app/public/products
        $allImages = Storage::files('public/products');
        // Obtener todos los archivos en el directorio 'products' dentro de 'storage/app/public'
    $files = Storage::disk('public')->files('products');

    // Si deseas solo los nombres de los archivos sin la ruta completa, puedes extraer solo los nombres
    $imageNames = array_map(function ($file) {
        return basename($file); // Devuelve solo el nombre del archivo sin la ruta
    }, $files);

        //Filtra las imágenes que no están en la base de datos
        $unusedImages = array_filter($imageNames, function ($filePath) use ($storedImageNames) {
           return !in_array(basename($filePath), $storedImageNames);
        });

        // Elimina las imágenes no utilizadas
        foreach ($unusedImages as $imagePath) {
            Storage::delete($imagePath);
        }
        // Recorremos el array de imágenes
    foreach ($unusedImages as $key => $imageName) {
        // Elimina la imagen en el directorio 'products' dentro de 'storage/app/public'
        $imagePath = 'products/' . $imageName; // Ruta completa a la imagen

        // Verifica si el archivo existe y luego lo elimina
        if (Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }
    }
        return response()->json([
            'message' => 'Unused images have been removed.',
            'removed_images' => array_map('basename', $unusedImages),
            'images' => $storedImages,
            'image_path' => $storedImageNames,
            'storage' => $imageNames,
            'filtro' => $unusedImages
        ]);
    }

    // public function update(Request $request, $id)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'name' => 'sometimes|required|string|max:255',
    //         'description' => 'sometimes|nullable|string',
    //         'price' => 'sometimes|required|numeric|min:0',
    //         'stock' => 'sometimes|required|integer|min:0',
    //         'SKU' => 'sometimes|required|string|max:255|unique:products,SKU,' . $id,
    //         'iva' => 'sometimes|required|boolean',
    //         'category_id' => 'sometimes|required|exists:categories,id',
    //         'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validación para las imágenes
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['error' => $validator->errors()], 400);
    //     }

    //     $product = Product::findOrFail($id);
    //     $product->update($request->only(['name', 'description', 'price', 'stock', 'SKU', 'iva', 'category_id']));

    //     // Manejo de imágenes (eliminar y agregar nuevas si se proporcionan)
    //     if ($request->hasFile('images')) {
    //         // Elimina imágenes existentes
    //         foreach ($product->images as $image) {
    //             Storage::disk('public')->delete($image->image_path);
    //             $image->delete();
    //         }

    //         // Agrega las nuevas imágenes
    //         foreach ($request->file('images') as $index => $image) {
    //             $path = $image->store('images', 'public');
    //             ProductImage::create([
    //                 'product_id' => $product->id,
    //                 'image_path' => $path,
    //                 'top' => $index + 1
    //             ]);
    //         }
    //     }

    //     return response()->json(['message' => 'Product updated successfully', 'data' => $product->load('images')], 200);
    // }

    // public function destroy($id)
    // {
    //     $product = Product::findOrFail($id);

    //     // Elimina imágenes asociadas
    //     foreach ($product->images as $image) {
    //         Storage::disk('public')->delete($image->image_path);
    //         $image->delete();
    //     }

    //     $product->delete();

    //     return response()->json(['message' => 'Product deleted successfully'], 200);
    // }
    public function getProductsWithQuantities(Request $request)
    {
        // Validar que el input sea un array
        $request->validate([
            'products' => 'required|array',
            'products.*.id' => 'required|integer|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        // Obtener los productos junto con sus relaciones
        $productIds = collect($request->input('products'))->pluck('id')->toArray();
        $products = Product::with(['category', 'specifications', 'images'])
            ->whereIn('id', $productIds)
            ->get();

        // Mapear los productos para añadir la cantidad
        $productsWithQuantities = $products->map(function ($product) use ($request) {
            $quantity = collect($request->input('products'))->firstWhere('id', $product->id)['quantity'];
            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'stock' => $product->stock,
                'SKU' => $product->SKU,
                'iva' => $product->iva,
                'category' => $product->category,
                'specifications' => $product->specifications,
                'images' => $product->images,
                'quantity' => $quantity, // Añadir la cantidad
            ];
        });

        return response()->json($productsWithQuantities);
    }
    public function getProductsCart(Request $request)
    {
        // Validar que el input sea un array
        $request->validate([
            'products' => 'required|array',
            'products.*.id' => 'required|integer|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        // Obtener los productos que están en stock (stock >= 1) junto con sus relaciones
        $productIds = collect($request->input('products'))->pluck('id')->toArray();
        $products = Product::with(['category', 'images' => function ($query) {
            $query->where('top', 1); // Solo obtener la imagen top 1
        }])
            ->whereIn('id', $productIds)
            ->where('stock', '>=', 1) // Solo productos en stock
            ->get();

        // Mapear los productos para añadir la cantidad
        $productsWithQuantities = $products->map(function ($product) use ($request) {
            $quantity = collect($request->input('products'))->firstWhere('id', $product->id)['quantity'];

            // Obtener solo la URL de la imagen top 1
            $topImage = $product->images->first(); // Solo un elemento si existe

            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'stock' => $product->stock,
                'SKU' => $product->SKU,
                'iva' => $product->iva,
                'category' => $product->category,
                'image_url' => $topImage ? $topImage->image_path : null, // URL de la imagen o null si no existe
                'quantity' => $quantity, // Añadir la cantidad
            ];
        });

        return response()->json($productsWithQuantities);
    }

    public function getAllProductsDetails()
    {
        // Obtener todos los productos junto con sus relaciones
        $products = Product::with(['category', 'specifications', 'images'])->get();

        // Mapear los productos para añadir la cantidad (si necesitas un valor predeterminado)
        $productsAllDetails = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'stock' => $product->stock,
                'SKU' => $product->SKU,
                'iva' => $product->iva,
                'category' => $product->category,
                'specifications' => $product->specifications,
                'images' => $product->images,
                /* 'quantity' => 0, */ // Puedes establecer un valor predeterminado si es necesario
            ];
        });

        return response()->json($productsAllDetails);
    }
    public function getAllProducts()
    {
        // Obtener todos los productos junto con sus relaciones
        $products = Product::with(['category', 'images'])->get();

        // Mapear los productos para añadir la cantidad (si necesitas un valor predeterminado)
        $productsAll = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'stock' => $product->stock,
                'SKU' => $product->SKU,
                'iva' => $product->iva,
                'category' => $product->category,
                'images' => $product->images,
                /* 'quantity' => 0, */ // Puedes establecer un valor predeterminado si es necesario
            ];
        });

        return response()->json($productsAll);
    }

    public function getInStockProducts()
    {
        // Obtener productos donde el stock es mayor a 0
        $products = Product::where('stock', '>', 0)
            ->get(['id', 'stock']); // Selecciona solo los atributos id y stock

        return response()->json($products);
    }
    public function getStockById($id)
    {
        // Buscar el producto por ID
        $product = Product::find($id);

        // Verificar si el producto existe
        if ($product) {
            // Si el producto existe, retornar el stock
            return response()->json(['stock' => $product->stock]);
        } else {
            // Si el producto no existe, retornar 0
            return response()->json(['stock' => 0]);
        }
    }
}
