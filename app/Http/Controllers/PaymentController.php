<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function pay($orderId, $paymentMethodCode)
    {
        // Находим заказ
        $order = Order::findOrFail($orderId);
        
        // Проверяем, что заказ в статусе "На оплату"
        if ($order->status !== 'На оплату') {
            return response()->json([
                'error' => 'Order is not in pending status',
            ], 400);
        }
        
        // Проверяем соответствие способа оплаты
        $paymentMethod = PaymentMethod::where('code', $paymentMethodCode)->first();
        if (!$paymentMethod || $order->payment_method_id !== $paymentMethod->id) {
            return response()->json([
                'error' => 'Invalid payment method',
            ], 400);
        }
        
        // Генерируем confirm_api_url для API
        $confirmApiUrl = url("/api/orders/{$orderId}/confirm-payment");        
     
        // Возвращаем информацию о платеже и confirm_api_url
        return response()->json([
            'message' => 'Payment processing',
            'order_id' => $orderId,
            'payment_method' => $paymentMethodCode,
            'amount' => $order->total_amount,
            'confirm_api_url' => $confirmApiUrl
        ]);
    }
}
