<?php

namespace App\Services\Cart;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;

class CartService
{
    /**
     * Получить корзину пользователя с товарами
     *
     * @param User $user
     * @return Cart|null
     */
    public function getCart(User $user): ?Cart
    {
        return Cart::with('items.product')->where('user_id', $user->id)->first();
    }

    /**
     * Добавить товар в корзину
     *
     * @param User $user
     * @param int $productId
     * @param int $quantity
     * @return CartItem
     */
    public function addItemToCart(User $user, int $productId, int $quantity): CartItem
    {
        $cart = Cart::firstOrCreate(['user_id' => $user->id]);
        
        // Проверяем существование товара
        $product = Product::findOrFail($productId);
        
        // Добавляем или обновляем товар в корзине
        return CartItem::updateOrCreate(
            [
                'cart_id' => $cart->id,
                'product_id' => $productId,
            ],
            [
                'quantity' => $quantity,
            ]
        );
    }

    /**
     * Удалить товар из корзины
     *
     * @param User $user
     * @param int $productId
     * @return bool
     */
    public function removeItemFromCart(User $user, int $productId): bool
    {
        $cart = Cart::where('user_id', $user->id)->first();
        
        if (!$cart) {
            return false;
        }
        
        $deleted = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $productId)
            ->delete();
        
        return $deleted > 0;
    }
}
