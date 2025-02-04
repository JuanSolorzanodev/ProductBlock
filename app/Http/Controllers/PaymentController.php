<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Exception\ApiErrorException;

class PaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
    }

    public function createPaymentIntent(Request $request)
    {
        try {
            $amount = $request->input('amount');
            $currency = $request->input('currency', 'usd');

            $paymentIntent = PaymentIntent::create([
                'amount' => $amount,
                'currency' => $currency,
            ]);

            return response()->json([
                'clientSecret' => $paymentIntent->client_secret
            ]);
        } catch (ApiErrorException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function confirmPayment(Request $request)
    {
        try {
            $paymentIntentId = $request->input('payment_intent_id');
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
            $paymentIntent->confirm();

            return response()->json(['success' => true, 'status' => $paymentIntent->status]);
        } catch (ApiErrorException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = env('STRIPE_WEBHOOK_SECRET');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sigHeader, $endpointSecret
            );
        } catch(\UnexpectedValueException $e) {
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Handle the event
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object;
                // Handle successful payment
                break;
            case 'payment_intent.payment_failed':
                $paymentIntent = $event->data->object;
                // Handle failed payment
                break;
            default:
                // Unexpected event type
                return response()->json(['error' => 'Unexpected event type'], 400);
        }

        return response()->json(['success' => true]);
    }
}



    // protected $stripeService;

    // public function __construct(StripeService $stripeService)
    // {
    //     $this->stripeService = $stripeService;
    // }

    // public function index()
    // {
    //     return response()->json(Payment::all(), 200);
    // }

    // public function store(Request $request)
    // {
    //     // Validación de los datos recibidos
    //     $validator = Validator::make($request->all(), [
    //         'order_id' => 'required|exists:orders,id',
    //         'first_name' => 'required|string|max:255',
    //         'last_name' => 'required|string|max:255',
    //         'locality' => 'required|string|max:255',
    //         'address' => 'required|string|max:255',
    //         'postal_code' => 'required|string|max:20',
    //         'phone' => 'required|string|max:20',
    //         'country' => 'required|string|max:255',
    //         'province' => 'required|string|max:255',
    //         'canton' => 'required|string|max:255',
    //         'amount' => 'required|numeric|min:0',
    //         'payment_method' => 'required|string|max:255',
    //         'status' => 'required|string|max:255',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['error' => $validator->errors()], 400);
    //     }

    //     // Configuración de Stripe
    //     Stripe::setApiKey(env('STRIPE_TEST_SK'));  // Asegúrate de tener configurada tu clave secreta de Stripe en .env

    //     try {
    //         // Crear el PaymentIntent en Stripe
    //         $paymentIntent = PaymentIntent::create([
    //             'amount' => $request->amount * 100, // Stripe maneja la cantidad en centavos
    //             'currency' => 'usd',  // Cambia la moneda según lo que estés usando
    //             'payment_method' => $request->payment_method,
    //             'confirmation_method' => 'manual',
    //             'confirm' => true,
    //         ]);

    //         // Verificar el estado del pago
    //         if ($paymentIntent->status == 'succeeded') {
    //             // Crear el registro de pago en la base de datos
    //             $payment = Payment::create([
    //                 'order_id' => $request->order_id,
    //                 'first_name' => $request->first_name,
    //                 'last_name' => $request->last_name,
    //                 'locality' => $request->locality,
    //                 'address' => $request->address,
    //                 'postal_code' => $request->postal_code,
    //                 'phone' => $request->phone,
    //                 'country' => $request->country,
    //                 'province' => $request->province,
    //                 'canton' => $request->canton,
    //                 'amount' => $request->amount,
    //                 'payment_method' => $request->payment_method,
    //                 'status' => 'success', // Estado de pago exitoso
    //             ]);

    //             return response()->json([
    //                 'message' => 'Payment created successfully',
    //                 'payment' => $payment,
    //             ], 201);
    //         } else {
    //             return response()->json([
    //                 'error' => 'Payment failed',
    //                 'payment_intent_status' => $paymentIntent->status
    //             ], 400);
    //         }
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'error' => 'Payment failed: ' . $e->getMessage(),
    //         ], 500);
    //     }
    // }

    // public function show($id)
    // {
    //     $payment = Payment::find($id);

    //     if (!$payment) {
    //         return response()->json(['error' => 'Payment not found'], 404);
    //     }

    //     return response()->json($payment, 200);
    // }

    // public function update(Request $request, $id)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'order_id' => 'required|exists:orders,id',
    //         'first_name' => 'required|string|max:255',
    //         'last_name' => 'required|string|max:255',
    //         'locality' => 'required|string|max:255',
    //         'address' => 'required|string|max:255',
    //         'postal_code' => 'required|string|max:20',
    //         'phone' => 'required|string|max:20',
    //         'country' => 'required|string|max:255',
    //         'province' => 'required|string|max:255',
    //         'canton' => 'required|string|max:255',
    //         'amount' => 'required|numeric|min:0',
    //         'payment_method' => 'required|string|max:255',
    //         'status' => 'required|string|max:255',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['error' => $validator->errors()], 400);
    //     }

    //     $payment = Payment::findOrFail($id);
    //     $payment->update($request->all());
    //     return response()->json(['message' => 'Payment updated successfully', 'data' => $payment], 200);
    // }

    // public function destroy($id)
    // {
    //     $payment = Payment::findOrFail($id);
    //     $payment->delete();
    //     return response()->json(['message' => 'Payment deleted successfully'], 204);
    // }

    // public function createOrderWithPayment(Request $request)
    // {
    //     $request->validate([
    //         'user_id' => 'required|exists:users,id', // ID del usuario que realiza la compra
    //         'items' => 'required|array', // Productos en el carrito
    //         'items.*.product_id' => 'required|exists:products,id', // ID del producto
    //         'items.*.quantity' => 'required|integer|min:1', // Cantidad del producto
    //         'total_amount' => 'required|numeric|min:0', // Total de la compra
    //         'payment_data' => 'required|array', // Datos de pago (tarjeta, etc.)
    //     ]);
    
    //     // Crear la orden
    //     $order = Order::create([
    //         'user_id' => $request->user_id,
    //         'order_date' => now(),
    //         'total_amount' => $request->total_amount,
    //     ]);
    
    //     // Crear los ítems de la orden
    //     foreach ($request->items as $item) {
    //         OrderItem::create([
    //             'order_id' => $order->id,
    //             'product_id' => $item['product_id'],
    //             'quantity' => $item['quantity'],
    //             'price' => Product::find($item['product_id'])->price,
    //             'subtotal' => $item['quantity'] * Product::find($item['product_id'])->price,
    //         ]);
    //     }
    
    //     // Crear el pago
    //     $payment = Payment::create([
    //         'order_id' => $order->id,
    //         'amount' => $request->total_amount,
    //         'payment_method' => 'credit_card', // O el método de pago seleccionado
    //         'status' => 'pending',
    //     ]);
    
    //     // Procesar el pago con Stripe (o cualquier otro gateway)
    //     $paymentIntent = $this->stripeService->createPaymentIntent($request->total_amount);
    
    //     // Actualizar el estado del pago
    //     $payment->update(['status' => 'completed']);
    
    //     return response()->json([
    //         'order_id' => $order->id,
    //         'payment_id' => $payment->id,
    //         'clientSecret' => $paymentIntent->client_secret,
    //     ]);
    // }
