<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodsTableSeeder extends Seeder
{
    public function run()
    {
        $paymentMethods = [
            [
                'name' => 'Credit Card',
                'code' => 'credit_card',
                'active' => true,
            ],
            [
                'name' => 'PayPal',
                'code' => 'paypal',
                'active' => true,
            ],
            [
                'name' => 'Bank Transfer',
                'code' => 'bank_transfer',
                'active' => true,
            ],
        ];

        foreach ($paymentMethods as $method) {
            PaymentMethod::create($method);
        }
    }
}
