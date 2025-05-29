<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => ucfirst(fake()->words(rand(2, 4), true)), 
            'description' => fake()->paragraph(),
            'price' => fake()->randomElement([19.99, 29.99, 49.99, 79.99, 99.99, 129.99, 149.99, 199.99, 249.99, 299.99, 399.99, 499.99, 599.99, 699.99, 799.99]),
            'created_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'updated_at' => now(),
        ];
    }
}