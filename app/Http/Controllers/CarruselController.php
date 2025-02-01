<?php

namespace App\Http\Controllers;

use App\Models\CarruselImage;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Cloudinary\Api\Admin\AdminApi;
class CarruselController extends Controller
{
    // Obtener todas las imágenes (para mostrar en el carrusel)
    public function index()
    {
        
        $images = CarruselImage::all();
        return response()->json($images);
    }

    // Subir nuevas imágenes al carrusel
    public function store(Request $request)
{
    // Validar que el campo 'images' sea un array de archivos de imagen
    $request->validate([
        'images' => 'required|array',
        'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // Ajusta el tamaño máximo si es necesario
    ]);

    $uploadedImages = []; // Array para almacenar las imágenes subidas

    // Verificar si hay archivos en el campo 'images'
    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $image) {
            // Subir la imagen a Cloudinary en el folder "carousel/"
            $uploadedFileUrl = Cloudinary::upload($image->getRealPath(), [
                'folder' => 'carousel/',
            ])->getSecurePath();

            // Crear un nombre para la imagen (puedes personalizarlo según tus necesidades)
            $imageName = $image->getClientOriginalName(); // Nombre original del archivo
            $imageSize = $image->getSize(); // Tamaño del archivo en bytes

            // Guardar en la base de datos
            $newImage = CarruselImage::create([
                'image_path' => $uploadedFileUrl,
                'name' => $imageName,  // Establecer el nombre
                'size' => $imageSize,  // Establecer el tamaño
                'top' => null,          // Establecer top como null
            ]);

            $uploadedImages[] = $newImage; // Agregar la imagen subida al array
        }
    }

    return response()->json([
        'message' => 'Images uploaded successfully',
        'data' => $uploadedImages,
    ], 201);
}

public function destroy($id)
{
    // Buscar la imagen por su ID
    $image = CarruselImage::find($id);

    // Verificar si la imagen existe
    if (!$image) {
        return response()->json([
            'message' => 'Image not found',
        ], 404);
    }

    // Extraer el public_id de la URL de Cloudinary
    $urlParts = explode('/', $image->image_path);
    $fileName = end($urlParts); // "r8cq4adsfbkpnmv1tdxh.jpg"
    $fileNameWithoutExtension = pathinfo($fileName, PATHINFO_FILENAME); // "r8cq4adsfbkpnmv1tdxh"
    $publicId = 'carousel/' . $fileNameWithoutExtension; // "carousel/r8cq4adsfbkpnmv1tdxh"

    error_log("Public ID: " . $publicId); // Verificar el public_id en los logs

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

            return response()->json([
                'message' => 'Image deleted successfully',
            ], 200);
        } else {
            return response()->json([
                'message' => 'Failed to delete image from Cloudinary: Invalid public_id',
            ], 500);
        }
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Failed to delete image from Cloudinary',
            'error' => $e->getMessage(),
        ], 500);
    }
}
public function destroyPermanent($id)
{
    // Buscar la imagen por su ID
    $image = CarruselImage::find($id);

    // Verificar si la imagen existe
    if (!$image) {
        return response()->json([
            'message' => 'Image not found',
        ], 404);
    }

    // Extraer el public_id de la URL de Cloudinary
    $urlParts = explode('/', $image->image_path);
    $fileName = end($urlParts); // "r8cq4adsfbkpnmv1tdxh.jpg"
    $fileNameWithoutExtension = pathinfo($fileName, PATHINFO_FILENAME); // "r8cq4adsfbkpnmv1tdxh"
    $publicId = 'carousel/' . $fileNameWithoutExtension; // "carousel/r8cq4adsfbkpnmv1tdxh"

    error_log("Public ID: " . $publicId); // Verificar el public_id en los logs

    // Eliminar la imagen de Cloudinary usando AdminApi
    try {
        $adminApi = new AdminApi(); // Crear una instancia de AdminApi
        $result = $adminApi->deleteAssets([$publicId], [
            "resource_type" => "image",
            "type" => "upload",
            "invalidate" => true, // Eliminar de la caché CDN y borrar permanentemente
        ]);

        // Verificar si la eliminación en Cloudinary fue exitosa
        if (isset($result['deleted'][$publicId]) && $result['deleted'][$publicId] === 'deleted') {
            // Eliminar el registro de la base de datos
            $image->delete();

            return response()->json([
                'message' => 'Image deleted successfully',
            ], 200);
        } else {
            return response()->json([
                'message' => 'Failed to delete image from Cloudinary: Invalid public_id',
            ], 500);
        }
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Failed to delete image from Cloudinary',
            'error' => $e->getMessage(),
        ], 500);
    }
}


}
