<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
public function checkout(Request $request)
{
    $request->validate([
        'payment_method_id' => 'required|exists:payment_methods,id',
    ]);

    $user = $request->user();
    $cart = Cart::with('items.product')->where('user_id', $user->id)->first();
    
    if (!$cart || $cart->items->isEmpty()) {
        return response()->json([
            'message' => 'Cart is empty',
        ], 400);
    }
    
    $paymentMethod = PaymentMethod::findOrFail($request->payment_method_id);
    
    // Calculate total amount
    $totalAmount = 0;
    foreach ($cart->items as $item) {
        $totalAmount += $item->product->price * $item->quantity;
    }
    
    // Create order
    $order = Order::create([
        'user_id' => $user->id,
        'payment_method_id' => $paymentMethod->id,
        'status' => 'pending',
        'total_amount' => $totalAmount,
    ]);
    
    // Create order items
    foreach ($cart->items as $item) {
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $item->product_id,
            'quantity' => $item->quantity,
            'price' => $item->product->price,
        ]);
    }
    
    // Generate payment link - без токена
    $paymentLink = url("/pay/{$order->id}/{$paymentMethod->code}");
    
    $order->update([
        'payment_link' => $paymentLink,
    ]);
    
    // Delete cart
    $cart->items()->delete();
    $cart->delete();
    
    return response()->json([
        'message' => 'Order created successfully',
        'order' => $order,
        'payment_link' => $paymentLink,
    ]);
}

    public function confirmPayment($id)
    {
        $order = Order::findOrFail($id);
    
        if ($order->status !== 'pending') {
            return response()->json([
                'message' => 'Order is not in pending status',
            ], 400);
        }
    
        $order->update([
            'status' => 'paid',
        ]);
    
        return response()->json([
            'message' => 'Payment confirmed',
            'order' => $order,
        ]);
    }


    public function index(Request $request)
    {
        $user = $request->user();
        $query = Order::with(['paymentMethod', 'items.product'])
            ->where('user_id', $user->id);
        
        // Filter by status
        if ($request->has('status') && in_array($request->status, ['pending', 'paid', 'cancelled'])) {
            $query->where('status', $request->status);
        }
        
        // Sort by creation date
        $direction = $request->input('sort_by_date', 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy('created_at', $direction);
        
        $orders = $query->paginate(10);
        
        return response()->json($orders);
    }

    public function show($id, Request $request)
    {
        $user = $request->user();
        $order = Order::with(['paymentMethod', 'items.product'])
            ->where('user_id', $user->id)
            ->findOrFail($id);
        
        return response()->json($order);
    }
}
