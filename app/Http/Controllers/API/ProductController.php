<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();
        
        if ($request->has('sort_by_price')) {
            $direction = $request->input('sort_by_price') === 'desc' ? 'desc' : 'asc';
            $query->orderBy('price', $direction);
        }
        
        $products = $query->paginate(10);
        
        return response()->json($products);
    }

    public function show($id)
    {
        $product = Product::findOrFail($id);
        
        return response()->json($product);
    }
}
