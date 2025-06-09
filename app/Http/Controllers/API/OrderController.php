<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\CheckoutRequest;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * @OA\Tag(
 *     name="Orders",
 *     description="Операции с заказами"
 * )
 */

class OrderController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/orders/checkout",
     *     summary="Оформить заказ",
     *     tags={"Orders"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"payment_method_id"},
     *             @OA\Property(property="payment_method_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Заказ успешно создан",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Order created successfully"),
     *             @OA\Property(property="order", type="object"),
     *             @OA\Property(property="payment_link", type="string", example="http://example.com/pay/1/card")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Cart is empty or invalid data")
     * )
     */
public function checkout(CheckoutRequest $request)
{
    $user = $request->user();
    $cart = Cart::with('items.product')->where('user_id', $user->id)->first();
    
    if (!$cart || $cart->items->isEmpty()) {
        return response()->json([
            'message' => 'Cart is empty',
        ], 400);
    }
    
    $paymentMethod = PaymentMethod::findOrFail($request->payment_method_id);
    
    $totalAmount = 0;
    foreach ($cart->items as $item) {
        $totalAmount += $item->product->price * $item->quantity;
    }
    
    $order = Order::create([
        'user_id' => $user->id,
        'payment_method_id' => $paymentMethod->id,
        'status' => 'На оплату',
        'total_amount' => $totalAmount,
    ]);
    
    foreach ($cart->items as $item) {
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $item->product_id,
            'quantity' => $item->quantity,
            'price' => $item->product->price,
        ]);
    }
    
    $paymentLink = url("/pay/{$order->id}/{$paymentMethod->code}");
    
    $order->update([
        'payment_link' => $paymentLink,
    ]);
    
    $cart->items()->delete();
    $cart->delete();
    
    return response()->json([
        'message' => 'Order created successfully',
        'order' => $order,
        'payment_link' => $paymentLink,
    ]);
}


    /**
     * @OA\Post(
     *     path="/api/orders/{id}/confirm-payment",
     *     summary="Подтвердить оплату заказа",
     *     tags={"Orders"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID заказа",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Оплата подтверждена",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Payment confirmed"),
     *             @OA\Property(property="order", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Order is not in pending status"),
     *     @OA\Response(response=404, description="Order not found")
     * )
     */
    public function confirmPayment($id)
    {
        $order = Order::findOrFail($id);
    
        if ($order->status !== 'На оплату') {
            return response()->json([
                'message' => 'Order is not in pending status',
            ], 400);
        }
    
        $order->update([
            'status' => 'Оплачен',
        ]);
    
        return response()->json([
            'message' => 'Payment confirmed',
            'order' => $order,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/orders",
     *     summary="Получить список заказов пользователя",
     *     tags={"Orders"},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Фильтр по статусу (На оплату, Оплачен, Отменен)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort_by_date",
     *         in="query",
     *         description="Сортировка по дате (asc или desc)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Список заказов",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     )
     * )
     */

    public function index(Request $request)
    {
        $user = $request->user();
        $query = Order::with(['paymentMethod', 'items.product'])
            ->where('user_id', $user->id);
        
        // Filter by status
        if ($request->has('status') && in_array($request->status, ['На оплату', 'Оплачен', 'Отменен'])) {
            $query->where('status', $request->status);
        }
        
        // Sort by creation date
        $direction = $request->input('sort_by_date', 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy('created_at', $direction);
        
        $orders = $query->get();
        
        return response()->json($orders);
    }

    /**
     * @OA\Get(
     *     path="/api/orders/{id}",
     *     summary="Получить заказ по ID",
     *     tags={"Orders"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID заказа",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Информация о заказе",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=404, description="Order not found")
     * )
     */

    public function show($id, Request $request)
    {
        $user = $request->user();
        $order = Order::with(['paymentMethod', 'items.product'])
            ->where('user_id', $user->id)
            ->findOrFail($id);
        
        return response()->json($order);
    }
}
