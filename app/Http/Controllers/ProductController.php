<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\ProductImage;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Cloudinary\Cloudinary;
use Cloudinary\Api\Admin\AdminApi;
class ProductController extends Controller
{

    public function index(Request $request)
    {
        $query = $request->input('name');

        // Obtiene todos los productos con sus imágenes, filtrando por nombre si se proporciona
        $products = Product::with('images')
            ->when($query, function ($queryBuilder) use ($query) {
                return $queryBuilder->where('name', 'like', '%' . $query . '%');
            })->get();

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
        'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
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
    'packaging_type' => $request->input('packaging_type') ?? null,
    'material' => $request->input('material') ?? null,
    'usage_location' => $request->input('usage_location') ?? null,
    'color' => $request->input('color') ?? null,
    'load_capacity' => $request->input('load_capacity') ?? null,
    'country_of_origin' => $request->input('country_of_origin') ?? null,
    'warranty' => $request->input('warranty') ?? false,
    'number_of_pieces' => $request->input('number_of_pieces') ?? 1,
]);


    // Subir imágenes a Cloudinary y guardar las URLs
    $uploadedImages = [];
    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $key => $image) {
            $uploadedFileUrl = \Cloudinary::upload($image->getRealPath(), [
                'folder' => 'products/' . $product->id,
            ])->getSecurePath();

            // Guardar en la base de datos
            $product->images()->create([
                'image_path' => $uploadedFileUrl,
                'name' => $image->getClientOriginalName(), // Obtener el nombre original
                'size' => $image->getSize(), // Obtener el tamaño en bytes
                'top' => $key + 1,
            ]);
            $uploadedImages[] = $uploadedFileUrl;
        }
    }

    // Responder con el producto creado
    return response()->json([
        'message' => 'Producto creado con éxito',
        'data' => $product->load('specifications', 'images'),
        'images' => $uploadedImages,
    ], 201);
}
   

public function update(Request $request, $id)
{
    // Validar los datos del producto y las especificaciones
    $request->validate([
        'name' => 'nullable|string|max:255',
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
        'images.*.file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Imagen nueva
        'images.*.id' => 'nullable|integer',
        'images.*.image_path' => 'nullable|url', // URL de imagen ya subida
        'images.*.name' => 'nullable|string|max:255',
        'images.*.size' => 'nullable|string|max:255',
        'images.*.top' => 'nullable|integer',
        'deletedids' => 'nullable|array', // Validar que deletedids sea un array
        'deletedids.*' => 'nullable|string', // Validar que cada elemento del array sea un entero
    ]);

    $images = $request->input('images', []); // Datos de imágenes ya subidas
    $uploadedFiles = $request->file('images'); // Archivos de imágenes subidas
    $deletedIds = $request->input('deletedids', []);
    $imageData = [];

    // Buscar el producto a actualizar
    $product = Product::findOrFail($id);

    // Actualizar el producto
    $product->update([
        'name' => $request->input('name'),
        'description' => $request->input('description'),
        'price' => $request->input('price'),
        'stock' => $request->input('stock'),
        'SKU' => $request->input('SKU'),
        'iva' => $request->input('iva'),
        'category_id' => $request->input('category_id'),
    ]);

    // Actualizar las especificaciones del producto
    $product->specifications()->updateOrCreate(
        ['product_id' => $product->id],
        [
            'packaging_type' => $request->input('packaging_type') ?? null,
            'material' => $request->input('material') ?? null,
            'usage_location' => $request->input('usage_location') ?? null,
            'color' => $request->input('color') ?? null,
            'load_capacity' => $request->input('load_capacity') ?? null,
            'country_of_origin' => $request->input('country_of_origin') ?? null,
            'warranty' => $request->input('warranty') ?? false,
            'number_of_pieces' => $request->input('number_of_pieces') ?? 1,
        ]
    );

    // Manejar la eliminación de imágenes
    foreach ($deletedIds as $deletedId) {
        $image = ProductImage::find($deletedId);
        if ($image) {
            // Extraer el public_id de la URL de Cloudinary
            $urlParts = explode('/', $image->image_path);
            $fileName = end($urlParts); // "r8cq4adsfbkpnmv1tdxh.jpg"
            $fileNameWithoutExtension = pathinfo($fileName, PATHINFO_FILENAME); // "r8cq4adsfbkpnmv1tdxh"
            $publicId = 'products/' . $product->id . '/' . $fileNameWithoutExtension; // "products/4/r8cq4adsfbkpnmv1tdxh"

            // Eliminar la imagen de Cloudinary usando AdminApi
            try {
                $adminApi = new AdminApi(); // Crear una instancia de AdminApi
                $result = $adminApi->deleteAssets([$publicId], [
                    "resource_type" => "image",
                    "type" => "upload",
                ]);

                // Verificar si la eliminación en Cloudinary fue exitosa
                if (isset($result['deleted'][$publicId]) && $result['deleted'][$publicId] === 'deleted') {
                    // Eliminar el registro de la base de datos
                    $image->delete();
                }
            } catch (\Exception $e) {
                // Manejar el error si la eliminación falla
                Log::error('Failed to delete image from Cloudinary: ' . $e->getMessage());
            }
        }
    }

    // Manejar la actualización y creación de imágenes
    foreach ($images as $index => $image) {
        if (isset($image['id']) && isset($image['image_path']) && !isset($uploadedFiles[$index]['file'])) {
            // Imagen existente, solo actualizar el campo 'top'
            $productImage = ProductImage::find($image['id']);
            if ($productImage) {
                $productImage->update([
                    'top' => $image['top'] ?? ($index + 1),
                ]);
            }
        } elseif (isset($uploadedFiles[$index]['file'])) {
            // Nueva imagen, subir a Cloudinary y crear en la base de datos
            $file = $uploadedFiles[$index]['file'];
            $uploadedFileUrl = \Cloudinary::upload($file->getRealPath(), [
                'folder' => 'products/' . $product->id,
            ])->getSecurePath();

            // Guardar en la base de datos
            $product->images()->create([
                'image_path' => $uploadedFileUrl,
                'name' => $file->getClientOriginalName(), // Obtener el nombre original
                'size' => $file->getSize(), // Obtener el tamaño en bytes
                'top' => $image['top'] ?? ($index + 1),
            ]);
        }
    }

    // Responder con el producto actualizado
    return response()->json([
        'message' => 'Producto actualizado correctamente',
        'data' => $product,
        /* 'images' => $product->images, */
        'Eliminados'=> $deletedIds,
        /* 'busqueda para eliminar'=>$image,
        'id prodcuto' => $product->id, */
    ], 200);
}
public function destroy($id)
{
    // Buscar el producto por ID
    $product = Product::findOrFail($id);

    // Eliminar las imágenes del producto en Cloudinary y la base de datos
    foreach ($product->images as $image) {
        // Extraer el public_id de la URL de Cloudinary
        $urlParts = explode('/', $image->image_path);
        $fileName = end($urlParts); // "r8cq4adsfbkpnmv1tdxh.jpg"
        $fileNameWithoutExtension = pathinfo($fileName, PATHINFO_FILENAME); // "r8cq4adsfbkpnmv1tdxh"
        $publicId = 'products/' . $product->id . '/' . $fileNameWithoutExtension; // "products/4/r8cq4adsfbkpnmv1tdxh"

        // Eliminar la imagen de Cloudinary usando AdminApi
        try {
            $adminApi = new AdminApi(); // Crear una instancia de AdminApi
            $result = $adminApi->deleteAssets([$publicId], [
                "resource_type" => "image",
                "type" => "upload",
            ]);

            // Verificar si la eliminación en Cloudinary fue exitosa
            if (isset($result['deleted'][$publicId]) && $result['deleted'][$publicId] === 'deleted') {
                // Eliminar el registro de la base de datos
                $image->delete();
            }
        } catch (\Exception $e) {
            // Manejar el error si la eliminación falla
            Log::error('Failed to delete image from Cloudinary: ' . $e->getMessage());
        }
    }

    // Eliminar las especificaciones del producto
    $product->specifications()->delete();

    // Eliminar el producto
    $product->delete();

    // Responder con un mensaje de éxito
    return response()->json([
        'message' => 'Producto eliminado correctamente',
    ], 200);
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

    public function getProductDetails($id)
{
    // Validar que el producto exista
    $product = Product::with(['category', 'specifications', 'images'])->find($id);

    if (!$product) {
        return response()->json([
            'message' => 'Producto no encontrado'
        ], 404);
    }

    // Retornar el producto con sus detalles
    return response()->json([
        'message' => 'Producto encontrado',
        'data' => $product
    ], 200);
}

}
