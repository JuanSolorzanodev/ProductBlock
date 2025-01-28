<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ImageController extends Controller
{
    // Obtener todas las imágenes (para mostrar en el carrusel)
    public function index()
    {
        
        $images = Image::all();
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

                // Guardar en la base de datos
                $newImage = Image::create([
                    'image_path' => $uploadedFileUrl,
                ]);

                $uploadedImages[] = $newImage; // Agregar la imagen subida al array
            }
        }

        return response()->json([
            'message' => 'Images uploaded successfully',
            'data' => $uploadedImages,
        ], 201);
    }

    // Eliminar una imagen del carrusel (soft delete)
    // public function destroy($id)
    // {
    //     $image = Image::findOrFail($id);
    //     $image->delete();

    //     return response()->json(['message' => 'Image deleted successfully']);
    // }
    /**
 * Extrae el public_id de la URL de la imagen de Cloudinary.
 *
 * @param string $url
 * @return string
 */
private function extractPublicId($url)
{
    // Ejemplo de URL: https://res.cloudinary.com/<cloud_name>/image/upload/v1234567890/folder_name/file_name.jpg
    $path = parse_url($url, PHP_URL_PATH);
    $segments = explode('/', $path);

    // Remueve el prefijo de la carpeta y extensión del archivo (si es necesario)
    $fileNameWithExtension = end($segments);
    $publicId = pathinfo($fileNameWithExtension, PATHINFO_FILENAME);

    // Si estás usando carpetas, concatena con los segmentos de la carpeta
    if (count($segments) > 4) {
        $folder = implode('/', array_slice($segments, -2, 1));
        $publicId = $folder . '/' . $publicId;
    }

    return $publicId;
}



    public function destroy($id)
{
    // Encuentra la imagen por su ID
    $image = Image::findOrFail($id);

    // Obtén el public_id de la imagen desde la URL guardada (asegúrate de guardar el public_id en tu base de datos)
    $publicId = $this->extractPublicId($image->image_path);

    // Elimina la imagen de Cloudinary
    try {
        Cloudinary::destroy($publicId);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to delete image from Cloudinary: ' . $e->getMessage()], 500);
    }

    // Realiza un borrado lógico en la base de datos
    $image->delete();

    return response()->json(['message' => 'Image deleted successfully']);
}






    // Restaurar una imagen eliminada del carrusel
    public function restore($id)
    {
        $image = Image::withTrashed()->findOrFail($id);
        $image->restore();

        return response()->json(['message' => 'Image restored successfully']);
    }
}
