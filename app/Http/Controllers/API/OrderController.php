<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\CheckoutRequest;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Services\Order\OrderService;
use Illuminate\Http\JsonResponse;
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

    protected $orderService;
    
    /**
     * Конструктор с внедрением зависимости OrderService
     *
     * @param OrderService $orderService
     */
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }
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
public function checkout(CheckoutRequest $request): JsonResponse
{
        try {
            $order = $this->orderService->checkout(
                $request->user(),
                $request->payment_method_id
            );
            
            return response()->json([
                'message' => 'Order created successfully',
                'order' => $order,
                'payment_link' => $order->payment_link,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
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
    public function confirmPayment($id): JsonResponse
    {
        try {
            $order = $this->orderService->confirmPayment($id);
            
            return response()->json([
                'message' => 'Payment confirmed',
                'order' => $order,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
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

    public function index(Request $request): JsonResponse
    {
        $orders = $this->orderService->getUserOrders(
            $request->user(),
            $request->status,
            $request->input('sort_by_date', 'desc')
        );
        
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

    public function show($id, Request $request): JsonResponse
    {
        try {
            $order = $this->orderService->getUserOrder(
                $request->user(),
                $id
            );

            return response()->json($order);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Order not found',
            ], 404);
        }
    }
}
