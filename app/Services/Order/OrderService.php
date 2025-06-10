<?php

namespace App\Services\Order;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Создать заказ из корзины пользователя
     *
     * @param User $user
     * @param int $paymentMethodId
     * @return Order
     * @throws \Exception
     */
    public function checkout(User $user, int $paymentMethodId): Order
    {
        $cart = Cart::with('items.product')->where('user_id', $user->id)->first();
        
        if (!$cart || $cart->items->isEmpty()) {
            throw new \Exception('Cart is empty');
        }
        
        $paymentMethod = PaymentMethod::findOrFail($paymentMethodId);
        
        // Вычисляем общую сумму заказа
        $totalAmount = $this->calculateTotalAmount($cart);
        
        // Используем транзакцию для обеспечения целостности данных
        return DB::transaction(function () use ($user, $cart, $paymentMethod, $totalAmount) {
            // Создаем заказ
            $order = Order::create([
                'user_id' => $user->id,
                'payment_method_id' => $paymentMethod->id,
                'status' => 'На оплату',
                'total_amount' => $totalAmount,
            ]);
            
            // Создаем элементы заказа
            $this->createOrderItems($order, $cart);
            
            // Генерируем ссылку на оплату
            $paymentLink = url("/pay/{$order->id}/{$paymentMethod->code}");
            
            // Обновляем заказ со ссылкой на оплату
            $order->update([
                'payment_link' => $paymentLink,
            ]);
            
            // Очищаем корзину
            $cart->items()->delete();
            $cart->delete();
            
            return $order;
        });
    }
    
    /**
     * Подтвердить оплату заказа
     *
     * @param int $orderId
     * @return Order
     * @throws \Exception
     */
    public function confirmPayment(int $orderId): Order
    {
        $order = Order::findOrFail($orderId);
        
        if ($order->status !== 'На оплату') {
            throw new \Exception('Order is not in pending status');
        }
        
        $order->update([
            'status' => 'Оплачен',
        ]);
        
        return $order;
    }
    
    /**
     * Получить список заказов пользователя с фильтрацией и сортировкой
     *
     * @param User $user
     * @param string|null $status
     * @param string $sortDirection
     * @return Collection
     */
    public function getUserOrders(User $user, ?string $status = null, string $sortDirection = 'desc'): Collection
    {
        $query = Order::with(['paymentMethod', 'items.product'])
            ->where('user_id', $user->id);
        
        // Фильтрация по статусу
        if ($status && in_array($status, ['На оплату', 'Оплачен', 'Отменен'])) {
            $query->where('status', $status);
        }
        
        // Сортировка по дате создания
        $direction = $sortDirection === 'asc' ? 'asc' : 'desc';
        $query->orderBy('created_at', $direction);
        
        return $query->get();
    }
    
    /**
     * Получить детали конкретного заказа пользователя
     *
     * @param User $user
     * @param int $orderId
     * @return Order
     */
    public function getUserOrder(User $user, int $orderId): Order
    {
        return Order::with(['paymentMethod', 'items.product'])
            ->where('user_id', $user->id)
            ->findOrFail($orderId);
    }
    
    /**
     * Вычислить общую сумму заказа на основе корзины
     *
     * @param Cart $cart
     * @return float
     */
    private function calculateTotalAmount(Cart $cart): float
    {
        $totalAmount = 0;
        foreach ($cart->items as $item) {
            $totalAmount += $item->product->price * $item->quantity;
        }
        return $totalAmount;
    }
    
    /**
     * Создать элементы заказа на основе корзины
     *
     * @param Order $order
     * @param Cart $cart
     * @return void
     */
    private function createOrderItems(Order $order, Cart $cart): void
    {
        foreach ($cart->items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->product->price,
            ]);
        }
    }
}
