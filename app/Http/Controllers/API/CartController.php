<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\AddItemRequest;
use App\Http\Requests\Cart\RemoveItemRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Services\Cart\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Js;

/**
 * @OA\Tag(
 *     name="Cart",
 *     description="Операции с корзиной"
 * )
 */

class CartController extends Controller
{
    protected $cartService;

    /**
     * Конструктор с внедрением зависимости CartService
     *
     * @param CartService $cartService
     */
    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }


    /**
     * @OA\Post(
     *     path="/api/cart/add",
     *     summary="Добавить товар в корзину",
     *     tags={"Cart"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id", "quantity"},
     *             @OA\Property(property="product_id", type="integer", example=1),
     *             @OA\Property(property="quantity", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Товар добавлен в корзину",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product added to cart"),
     *             @OA\Property(property="cart_item", type="object")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Ошибка валидации")
     * )
     */
    public function addItem(AddItemRequest $request): JsonResponse
    {
        $cartItem = $this->cartService->addItemToCart(
            $request->user(),
            $request->product_id,
            $request->quantity
        );
        
        return response()->json([
            'message' => 'Product added to cart',
            'cart_item' => $cartItem,
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/cart/remove",
     *     summary="Удалить товар из корзины",
     *     tags={"Cart"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id"},
     *             @OA\Property(property="product_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Товар удален из корзины",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product removed from cart")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Товар или корзина не найдены",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product not found in cart")
     *         )
     *     )
     * )
     */
    public function removeItem(RemoveItemRequest $request): JsonResponse
    {
        $result = $this->cartService->removeItemFromCart(
            $request->user(),
            $request->product_id
        );
        
        if ($result) {
            return response()->json([
                'message' => 'Product removed from cart',
            ]);
        }
        
        return response()->json([
            'message' => 'Product not found in cart',
        ], 404);
    }

    /**
     * @OA\Get(
     *     path="/api/cart",
     *     summary="Получить текущую корзину пользователя",
     *     tags={"Cart"},
     *     @OA\Response(
     *         response=200,
     *         description="Корзина найдена",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Корзина не найдена",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cart not found")
     *         )
     *     )
     * )
     */
    public function getCart(Request $request)
    {
        $cart = $this->cartService->getCart($request->user());
        
        if (!$cart) {
            return response()->json([
                'message' => 'Cart not found',
            ], 404);
        }
        
        return response()->json($cart);
    }
}
