<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // Obtener todos los usuarios (sin eliminados por defecto, con búsqueda opcional)
    public function index(Request $request)
    {
        $search = $request->query('search'); // Parámetro de búsqueda opcional
        $includeDeleted = $request->query('includeDeleted'); // Opcional para incluir eliminados
    
        $query = $includeDeleted ? User::withTrashed() : User::query(); // Incluir eliminados si es necesario
    
        // Aplicar búsqueda si hay un término
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%'); // Buscar también por teléfono
            });
        }
    
        // Incluir la relación con roles y seleccionar los campos deseados
        $users = $query->with('role:id,name')->get(['id', 'name', 'email', 'phone', 'role_id', 'created_at']);
    
        return response()->json($users);
    }
    

  // Eliminar un usuario (Soft Delete)
public function destroy($id)
{
    $user = User::findOrFail($id);
    $user->delete();
    return response()->json(['message' => 'Usuario eliminado correctamente']);
}

// Restaurar un usuario eliminado
public function restore($id)
{
    $user = User::withTrashed()->findOrFail($id);
    $user->restore();
    return response()->json(['message' => 'Usuario restaurado correctamente']);
}

// Eliminar un usuario permanentemente
public function forceDelete($id)
{
    $user = User::withTrashed()->findOrFail($id);
    $user->forceDelete();
    return response()->json(['message' => 'Usuario eliminado permanentemente']);
}

// Actualizar un usuario existente
public function update(Request $request, $id)
{
    // Validación de los campos recibidos
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $id, // Validación ignorando el actual
        'phone' => 'nullable|string|max:20', // Validación del campo phone
        'role_id' => 'nullable|exists:roles,id', // Validación para que el role_id sea válido
    ]);

    // Buscar el usuario y actualizar
    $user = User::findOrFail($id);
    $user->update([
        'name' => $request->input('name'),
        'email' => $request->input('email'),
        'phone' => $request->input('phone', $user->phone), // Solo actualiza el teléfono si se proporciona
        'role_id' => $request->input('role_id', $user->role_id), // Solo actualiza el role si se proporciona
    ]);

    return response()->json(['message' => 'Usuario actualizado correctamente.']);
}

}
