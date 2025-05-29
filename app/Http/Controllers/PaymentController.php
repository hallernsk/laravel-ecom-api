<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function pay($orderId, $paymentMethodCode)
    {
        $order = Order::findOrFail($orderId);
        
        if ($order->status !== 'На оплату') {
            return response()->json([
                'error' => 'Order is not in pending status',
            ], 400);
        }
        
        $paymentMethod = PaymentMethod::where('code', $paymentMethodCode)->first();
        if (!$paymentMethod || $order->payment_method_id !== $paymentMethod->id) {
            return response()->json([
                'error' => 'Invalid payment method',
            ], 400);
        }
        
        $confirmApiUrl = url("/api/orders/{$orderId}/confirm-payment");        
     
        return response()->json([
            'message' => 'Payment processing',
            'order_id' => $orderId,
            'payment_method' => $paymentMethodCode,
            'amount' => $order->total_amount,
            'confirm_api_url' => $confirmApiUrl
        ]);
    }
}
