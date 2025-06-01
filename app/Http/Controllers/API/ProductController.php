<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Products",
 *     description="Операции с товарами"
 * )
 */
class ProductController extends Controller
{
/**
 * @OA\Get(
 *     path="/api/products",
 *     summary="Получить список товаров",
 *     tags={"Products"},
 *     @OA\Parameter(
 *         name="sort_by_price",
 *         in="query",
 *         description="Сортировка по цене (asc или desc)",
 *         required=false,
 *         @OA\Schema(type="string", enum={"asc", "desc"})
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="OK",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="name", type="string", example="Пицца Маргарита"),
 *                     @OA\Property(property="description", type="string", example="Вкусная пицца с сыром"),
 *                     @OA\Property(property="price", type="number", format="float", example=299.99)
 *                 )
 *             )
 *         )
 *     )
 * )
 */
    public function index(Request $request)
    {
        $query = Product::query();
        
        if ($request->has('sort_by_price')) {
            $direction = $request->input('sort_by_price') === 'desc' ? 'desc' : 'asc';
            $query->orderBy('price', $direction);
        }
        
        $products = $query->paginate(10);;
        
        return response()->json($products);
    }

/**
 * @OA\Get(
 *     path="/api/products/{id}",
 *     summary="Получить товар по ID",
 *     tags={"Products"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID товара",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="OK",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="name", type="string", example="Пицца Маргарита"),
 *             @OA\Property(property="description", type="string", example="Вкусная пицца с сыром"),
 *             @OA\Property(property="price", type="number", format="float", example=299.99)
 *         )
 *     ),
 *     @OA\Response(response=404, description="Товар не найден")
 * )
 */
    public function show($id)
    {
        $product = Product::findOrFail($id);
        
        return response()->json($product);
    }
}
