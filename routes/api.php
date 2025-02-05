<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    CartController, CategoryController, CustomerController,StripeController,
    OrderController, PaymentController, ProductController, SupplierController,AuthController,CarruselController, RoleController, UserController,OrderItemController
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
Route::get('/carrusel', [CarruselController::class, 'index']);
Route::post('/carrusel', [CarruselController::class, 'store']);
Route::delete('/carrusel/{id}', [CarruselController::class, 'destroy']);

/* user rutes */
Route::get('/users', [UserController::class, 'index']); // Obtener usuarios
Route::put('/users/{id}', [UserController::class, 'update']); // Actualizar usuario
Route::delete('/users/{id}', [UserController::class, 'destroy']); // Soft delete
Route::patch('/users/{id}/restore', [UserController::class, 'restore']); // Restaurar
Route::delete('/users/{id}/force', [UserController::class, 'forceDelete']); // Eliminar permanentemente

//
Route::post('/products', [ProductController::class, 'store']);
Route::post('/uploadImage', [ProductController::class, 'uploadImage']);
Route::post('/products/{id}', [ProductController::class, 'update']);

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

Route::get('/roles', [RoleController::class, 'getRoles']);

Route::apiResource('order-items', OrderItemController::class);

Route::post('/payments', [PaymentController::class, 'store']);
Route::get('/payments/{id}', [PaymentController::class, 'show']);

Route::post('/create-checkout-session', [StripeController::class, 'createCheckoutSession']);
Route::get('/success', function() {
    return view('success'); // Crea una vista de éxito
});
Route::get('/cancel', function() {
    return view('cancel'); // Crea una vista de cancelación
});


Route::post('/create-payment-intent', [PaymentController::class, 'createPaymentIntent']);
Route::post('/confirm-payment', [PaymentController::class, 'confirmPayment']);
Route::post('/webhook', [PaymentController::class, 'webhook']);