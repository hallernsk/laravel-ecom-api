<?php

namespace Tests\Feature\API;

use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_checkout()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 100]);
        $paymentMethod = PaymentMethod::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/cart/add', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/checkout', [
            'payment_method_id' => $paymentMethod->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Order created successfully',
            ])
            ->assertJsonStructure([
                'order',
                'payment_link',
            ]);
    }

    public function test_user_can_view_orders()
    {
        $user = User::factory()->create();
        $paymentMethod = PaymentMethod::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'Оплачен',
            'payment_method_id' => $paymentMethod->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $order->id)
            ->assertJsonPath('0.status', 'Оплачен');
    }

    public function test_user_can_view_single_order()
    {
        $user = User::factory()->create();
        $paymentMethod = PaymentMethod::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'payment_method_id' => $paymentMethod->id,
            'status' => 'На оплату',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/orders/' . $order->id);

        $response->assertStatus(200)
            ->assertJsonPath('id', $order->id)
            ->assertJsonPath('status', 'На оплату');
    }

    public function test_order_payment_can_be_confirmed()
    {
        $paymentMethod = PaymentMethod::factory()->create();
        $order = Order::factory()->create([
            'status' => 'На оплату',
            'payment_method_id' => $paymentMethod->id,
        ]);

        $response = $this->postJson('/api/orders/' . $order->id . '/confirm-payment');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Payment confirmed',
            ])
            ->assertJsonPath('order.status', 'Оплачен');
    }
}
