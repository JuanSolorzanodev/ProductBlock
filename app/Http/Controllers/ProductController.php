<?php

namespace App\Http\Controllers;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('name');

        // Obtiene todos los productos con sus imágenes, filtrando por nombre si se proporciona
        $products = Product::with('images')
            ->when($query, function($queryBuilder) use ($query) {
                return $queryBuilder->where('name', 'like', '%' . $query . '%');
            })
            ->get();
    
        return response()->json(['data' => $products], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'SKU' => 'required|string|unique:products,SKU',
            'iva' => 'required|boolean',
            'category_id' => 'required|exists:categories,id',
            'images' => 'nullable|array',
            'images.*' => 'file|mimes:jpeg,png,jpg|max:2048', // Limita los archivos a imágenes de 2MB máximo
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
    
        // Crear el producto
        $product = Product::create($request->only(['name', 'description', 'price', 'stock', 'SKU', 'iva', 'category_id']));
    
        // Procesar y guardar las imágenes, si existen
        if ($request->has('images')) {
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('product_images', 'public'); // Guarda en storage/app/public/product_images
    
                // Crear la entrada en la tabla product_images
                $product->images()->create([
                    'image_path' => $path,
                    'top' => $index + 1, // Define la prioridad en base al orden en el que se envían las imágenes
                ]);
            }
        }
    
        return response()->json(['message' => 'Product created successfully', 'data' => $product->load('images')], 201);
    }
    

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
            'SKU' => 'sometimes|required|string|max:255|unique:products,SKU,' . $id,
            'iva' => 'sometimes|required|boolean',
            'category_id' => 'sometimes|required|exists:categories,id',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validación para las imágenes
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $product = Product::findOrFail($id);
        $product->update($request->only(['name', 'description', 'price', 'stock', 'SKU', 'iva', 'category_id']));

        // Manejo de imágenes (eliminar y agregar nuevas si se proporcionan)
        if ($request->hasFile('images')) {
            // Elimina imágenes existentes
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image->image_path);
                $image->delete();
            }

            // Agrega las nuevas imágenes
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('images', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $path,
                    'top' => $index + 1
                ]);
            }
        }

        return response()->json(['message' => 'Product updated successfully', 'data' => $product->load('images')], 200);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        // Elimina imágenes asociadas
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->image_path);
            $image->delete();
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully'], 200);
    }
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
        


}