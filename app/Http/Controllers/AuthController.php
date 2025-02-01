<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Validar los datos, incluyendo el phone opcional y role_id
        $data = $request->validate([
            'name'     => ['required', 'string'],
            'email'    => ['required', 'email', 'unique:users'],
            'phone'    => ['nullable', 'string', 'max:20'], // Validación del teléfono
            'password' => ['required', 'min:6'],
            'role_id'  => ['nullable', 'exists:roles,id'], // Validación de role_id, si es proporcionado
        ]);

        // Hashear la contraseña antes de crear el usuario
        $data['password'] = Hash::make($data['password']);

        // Si no se proporciona role_id, asignar un rol por defecto
        if (!isset($data['role_id'])) {
            $data['role_id'] = Role::where('name', 'customer')->first()->id; // Asignar rol "user" por defecto
        }

        // Crear el usuario
        $user = User::create($data);


        // Obtener el rol del usuario
        $role = $user->role;


        // Crear el token de acceso
        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ]);
    }

    public function login(Request $request)
    {
        // Validar los datos de ingreso
        $data = $request->validate([
            'email'    => ['required', 'email', 'exists:users,email'],
            'password' => ['required', 'min:6'],
        ]);

        $user = User::where('email', $data['email'])->first();

        // Comprobar si la contraseña coincide
        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json([
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        // Obtener el rol del usuario
        $role = $user->role;

        // Crear el token de acceso
        $token = $user->createToken('authToken')->plainTextToken;


        return response()->json([
            'user'  => $user,
            'token' => $token,
        ]);
    }
}

