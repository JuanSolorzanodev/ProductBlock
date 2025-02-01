<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    //

    // Obtener todos los roles
    public function getRoles()
    {
        $roles = Role::all(); // Obtener todos los roles
        return response()->json($roles);
    }

}
