<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class StripeController extends Controller
{
 /**
     * Crear una sesión de pago con Stripe Checkout.
     */
    public function createCheckoutSession(Request $request)
    {
        // Establecer la clave secreta de Stripe
        Stripe::setApiKey(env('STRIPE_TEST_SK'));

        // Datos de los productos que el cliente quiere comprar
        $cartItems = $request->input('cartItems'); // Este es el array de productos que envías desde el frontend

        // Crear la sesión de Stripe Checkout
        try {
            $checkoutSession = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => collect($cartItems)->map(function ($item) {
                    return [
                        'price_data' => [
                            'currency' => 'usd', // O la moneda que desees
                            'product_data' => [
                                'name' => $item['name'],
                            ],
                            'unit_amount' => $item['price'] * 100, // Stripe espera el monto en centavos
                        ],
                        'quantity' => $item['quantity'],
                    ];
                })->toArray(),
                'mode' => 'payment',
                'success_url' => route('success'), // Ruta de éxito después del pago
                'cancel_url' => route('cancel'),   // Ruta en caso de cancelación
            ]);

            // Retornar el ID de la sesión de Stripe
            return response()->json(['id' => $checkoutSession->id]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

}