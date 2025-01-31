<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    CartController, CategoryController, CustomerController,
    OrderController, PaymentController, ProductController, SupplierController,AuthController,ImageController,UserController,
};

// Ruta para obtener información del usuario autenticado
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Rutas para las API de recursos
// Route::apiResource('categories', CategoryController::class);
Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::get('/trashed', [CategoryController::class, 'trashed']); // Ver eliminados
    Route::post('/', [CategoryController::class, 'store']);
    Route::get('/{id}', [CategoryController::class, 'show']);
    Route::put('/{id}', [CategoryController::class, 'update']);
    Route::delete('/{id}', [CategoryController::class, 'destroy']);
    Route::patch('/restore/{id}', [CategoryController::class, 'restore']); // Restaurar
});
Route::get('/products', [ProductController::class, 'getAllProducts']);
Route::apiResource('customers', CustomerController::class);
Route::apiResource('orders', OrderController::class);
Route::apiResource('suppliers', SupplierController::class);
Route::apiResource('carts', CartController::class);
Route::apiResource('payments', PaymentController::class);

/* Route::post('/products/getByIds', [ProductController::class, 'getProductsWithQuantities']); */
Route::post('/products/getByIds', [ProductController::class, 'getProductsCart']);
Route::get('/products/in-stock', [ProductController::class, 'getInStockProducts']);
Route::get('/products/{id}/stock', [ProductController::class, 'getStockById']);

/*carrousel rutase */
Route::get('/images', [ImageController::class, 'index']);
Route::post('/images', [ImageController::class, 'store']);
Route::delete('/images/{id}', [ImageController::class, 'destroy']);
Route::patch('/images/{id}/restore', [ImageController::class, 'restore']);

/* user rutes */
Route::get('/users', [UserController::class, 'index']); // Obtener usuarios
Route::put('/users/{id}', [UserController::class, 'update']); // Actualizar usuario
Route::delete('/users/{id}', [UserController::class, 'destroy']); // Soft delete
Route::patch('/users/{id}/restore', [UserController::class, 'restore']); // Restaurar
Route::delete('/users/{id}/force', [UserController::class, 'forceDelete']); // Eliminar permanentemente

//
Route::post('/products', [ProductController::class, 'store']);

Route::put('/products/{id}', [ProductController::class, 'update']);

Route::post('/products/{id}/images', [ProductController::class, 'updateImages']);

Route::delete('/products/{id}', [ProductController::class, 'destroy']);

Route::get('/products/{id}/details', [ProductController::class, 'getProductDetails']);


Route::delete('/clean-unused-images', [ProductController::class, 'cleanUnusedImages']);


// Rutas adicionales para el carrito
Route::get('/cart_items/{customerId}', [CartController::class, 'getCartItems']); // Obtener items del carrito de un cliente
Route::delete('/cart_items/{cartItemId}', [CartController::class, 'removeFromCart']); // Eliminar item del carrito
Route::put('/cart_items/{cartItemId}', [CartController::class, 'updateCartItem']); // Actualizar item del carrito

// Ruta adicional para crear intención de pago
Route::post('/payments/intent', [PaymentController::class, 'createPaymentIntent']);

Route::post('/login', [AuthController::class, 'login']); 
Route::post('/register', [AuthController::class, 'register']);
