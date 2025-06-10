<?php

namespace App\Services\Product;

use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductService
{
    /**
     * Получить список продуктов с сортировкой по цене
     *
     * @param string|null $sortByPrice
     * @return LengthAwarePaginator
     */
    public function getProducts(?string $sortByPrice = null): LengthAwarePaginator
    {
        $query = Product::query();

        if ($sortByPrice && in_array($sortByPrice, ['asc', 'desc'])) {
            $query->orderBy('price', $sortByPrice);
        }

        return $query->paginate(10);
    }

    /**
     * Получить продукт по ID
     *
     * @param int $id
     * @return Product
     * @throws ModelNotFoundException
     */
    public function getProductById(int $id): Product
    {
        return Product::findOrFail($id);
    }
}
