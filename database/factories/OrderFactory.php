<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\PaymentMethod;
use App\Models\Order;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(), 
            'payment_method_id' => PaymentMethod::factory(), 
            'status' => $this->faker->randomElement(['На оплату', 'Оплачен', 'Отменен']),
            'payment_link' => $this->faker->url(),
            'total_amount' => $this->faker->randomFloat(2, 50, 1000), // Сумма от 50 до 1000
            'created_at' => $this->faker->dateTime(),
            'updated_at' => $this->faker->dateTime(),
        ];
    }
}