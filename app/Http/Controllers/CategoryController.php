<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * List all active categories.
     */
    public function index()
    {
        $categories = Category::whereNull('deleted_at')->get();

        return response()->json([
            'data' => $categories
        ], 200);
    }

    /**
     * Store a new category.
     */
    public function store(Request $request)
    {
        // Validar datos de entrada
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 422);
        }

        // Crear categoría
        $category = Category::create($request->only('name'));

        return response()->json([
            'message' => 'Category created successfully.',
            'data' => $category
        ], 201);
    }

    /**
     * Show a specific category.
     */
    public function show($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'error' => 'Category not found.'
            ], 404);
        }

        return response()->json([
            'data' => $category
        ], 200);
    }

    /**
     * Update a specific category.
     */
    public function update(Request $request, $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'error' => 'Category not found.'
            ], 404);
        }

        // Validar datos de entrada
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories,name,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 422);
        }

        // Actualizar categoría
        $category->update($request->only('name'));

        return response()->json([
            'message' => 'Category updated successfully.',
            'data' => $category
        ], 200);
    }

    /**
     * Soft delete a specific category.
     */
    public function destroy($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'error' => 'Category not found.'
            ], 404);
        }

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully.'
        ], 200);
    }

    /**
     * Restore a soft-deleted category.
     */
    public function restore($id)
    {
        $category = Category::withTrashed()->find($id);

        if (!$category) {
            return response()->json([
                'error' => 'Category not found or not deleted.'
            ], 404);
        }

        $category->restore();

        return response()->json([
            'message' => 'Category restored successfully.',
            'data' => $category
        ], 200);
    }

    /**
     * List all soft-deleted categories.
     */
    public function trashed()
    {
        $categories = Category::onlyTrashed()->get();

        return response()->json([
            'data' => $categories
        ], 200);
    }
}
