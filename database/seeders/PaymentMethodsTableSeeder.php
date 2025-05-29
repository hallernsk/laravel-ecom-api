<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodsTableSeeder extends Seeder
{
    public function run()
    {
        PaymentMethod::factory()->create([
            'name' => 'Credit Card',
            'code' => 'credit_card',
            'active' => true
        ]);

        PaymentMethod::factory()->create([
            'name' => 'PayPal',
            'code' => 'paypal',
            'active' => true
        ]);

        PaymentMethod::factory()->create([
            'name' => 'Bank Transfer',
            'code' => 'bank_transfer',
            'active' => true
        ]);
    }
}
