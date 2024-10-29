<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function index()
    {
        $carts = Cart::with('cartItems.product')->get();
        return response()->json($carts, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $cart = Cart::create($request->all());
        return response()->json(['message' => 'Cart created successfully', 'data' => $cart], 201);
    }

    public function show($id)
    {
        $cart = Cart::with('cartItems.product')->find($id);
        if (!$cart) {
            return response()->json(['error' => 'Cart not found'], 404);
        }
        return response()->json($cart, 200);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $cart = Cart::findOrFail($id);
        $cart->update($request->all());
        return response()->json(['message' => 'Cart updated successfully', 'data' => $cart], 200);
    }

    public function destroy($id)
    {
        $cart = Cart::findOrFail($id);
        $cart->delete();
        return response()->json(['message' => 'Cart deleted successfully'], 204);
    }

    public function getCartItems($customerId)
    {
        $cart = Cart::where('customer_id', $customerId)->with('cartItems.product')->first();
        if (!$cart) {
            return response()->json(['error' => 'Cart not found'], 404);
        }
        return response()->json($cart->cartItems, 200);
    }

    public function removeFromCart($customerId, $productId)
    {
        $cart = Cart::where('customer_id', $customerId)->first();
        if (!$cart) {
            return response()->json(['error' => 'Cart not found'], 404);
        }

        $cartItem = CartItem::where('cart_id', $cart->id)->where('product_id', $productId)->first();
        if ($cartItem) {
            $cartItem->delete();
        }
        return response()->json(['message' => 'Product removed from cart'], 200);
    }

    public function updateCartItem(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $cartItem = CartItem::findOrFail($id);
        $cartItem->update(['quantity' => $request->quantity]);

        return response()->json(['message' => 'Cart item updated successfully', 'data' => $cartItem], 200);
    }





}