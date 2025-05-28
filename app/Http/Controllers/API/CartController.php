<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function addItem(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $user = $request->user();
        $cart = Cart::firstOrCreate(['user_id' => $user->id]);
        
        $product = Product::findOrFail($request->product_id);
        
        $cartItem = CartItem::updateOrCreate(
            [
                'cart_id' => $cart->id,
                'product_id' => $request->product_id,
            ],
            [
                'quantity' => $request->quantity,
            ]
        );
        
        return response()->json([
            'message' => 'Product added to cart',
            'cart_item' => $cartItem,
        ]);
    }

    public function removeItem(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $user = $request->user();
        $cart = Cart::where('user_id', $user->id)->first();
        
        if (!$cart) {
            return response()->json([
                'message' => 'Cart not found',
            ], 404);
        }
        
        $deleted = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $request->product_id)
            ->delete();
        
        if ($deleted) {
            return response()->json([
                'message' => 'Product removed from cart',
            ]);
        }
        
        return response()->json([
            'message' => 'Product not found in cart',
        ], 404);
    }

    public function getCart(Request $request)
    {
        $user = $request->user();
        $cart = Cart::with('items.product')->where('user_id', $user->id)->first();
        
        if (!$cart) {
            return response()->json([
                'message' => 'Cart not found',
            ], 404);
        }
        
        return response()->json($cart);
    }
}
