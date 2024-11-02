<?php

namespace App\Http\Controllers;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
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
}