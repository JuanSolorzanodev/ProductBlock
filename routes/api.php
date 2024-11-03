<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    CartController, CategoryController, CustomerController,
    OrderController, PaymentController, ProductController, SupplierController
};

// Ruta para obtener información del usuario autenticado
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Rutas para las API de recursos
Route::apiResource('categories', CategoryController::class);
// Route::apiResource('products', ProductController::class);
Route::get('/products', [ProductController::class, 'getAllProducts']);
Route::apiResource('customers', CustomerController::class);
Route::apiResource('orders', OrderController::class);
Route::apiResource('suppliers', SupplierController::class);
Route::apiResource('carts', CartController::class);
Route::apiResource('payments', PaymentController::class);
Route::post('/products/getByIds', [ProductController::class, 'getProductsWithQuantities']);

// Rutas adicionales para el carrito
Route::get('/cart_items/{customerId}', [CartController::class, 'getCartItems']); // Obtener items del carrito de un cliente
Route::delete('/cart_items/{cartItemId}', [CartController::class, 'removeFromCart']); // Eliminar item del carrito
Route::put('/cart_items/{cartItemId}', [CartController::class, 'updateCartItem']); // Actualizar item del carrito

// Ruta adicional para crear intención de pago
Route::post('/payments/intent', [PaymentController::class, 'createPaymentIntent']);
