<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductsTableSeeder extends Seeder
{
    public function run()
    {
        $products = [
            [
                'name' => 'Smartphone',
                'description' => 'Latest model smartphone with high-end features',
                'price' => 999.99,
            ],
            [
                'name' => 'Laptop',
                'description' => 'Powerful laptop for work and gaming',
                'price' => 1499.99,
            ],
            [
                'name' => 'Headphones',
                'description' => 'Wireless noise-cancelling headphones',
                'price' => 299.99,
            ],
            [
                'name' => 'Smartwatch',
                'description' => 'Fitness tracker and smartwatch',
                'price' => 199.99,
            ],
            [
                'name' => 'Tablet',
                'description' => 'Lightweight tablet for entertainment and productivity',
                'price' => 599.99,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
