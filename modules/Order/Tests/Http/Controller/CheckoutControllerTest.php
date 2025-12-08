<?php

namespace Http\Controller;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Modules\Order\Models\Order;
use Modules\Order\Models\OrderLine;
use Modules\Order\Tests\OrderTestCase;
use Modules\Payment\PayBuddy;
use Modules\Product\Models\Product;
use PHPUnit\Framework\Attributes\Test;

class CheckoutControllerTest extends OrderTestCase
{
    #[Test]
    public function it_successfully_creates_an_order(): void
    {
        $user = User::factory()->create();
        $products = Product::factory()->count(2)->create(
            new Sequence(
                ['name' => 'Very expensive air frier', 'price_in_cents' => 10000, 'stock' => 10],
                ['name' => 'Macbook Pro M3', 'price_in_cents' => 50000, 'stock' => 10]
            )
        );

        $paymentToken = PayBuddy::validToken();

        $response = $this->actingAs($user)
                ->post(
                    route('order::checkout'),
                    [
                        'payment_token' => $paymentToken,
                        'products' => [
                            ['id' => $products->first()->id, 'quantity' => 1],
                            ['id' => $products->last()->id, 'quantity' => 1]
                        ]
                    ]
                );

        $response->assertStatus(201);

        $order = Order::query()->latest('id')->first();

        $this->assertTrue($order->user->is($user));
        $this->assertEquals(60000, $order->total_in_cents);
        $this->assertEquals('paid', $order->status);
        $this->assertEquals('PayBuddy', $order->payment_gateway);
        $this->assertEquals(36, strlen($order->payment_id));

        $this->assertCount(2, $order->lines);

        foreach ($products as $product) {
            /** @var OrderLine $orderLine */
            $orderLine = $order->lines->firstWhere('product_id', $product->id);

            $this->assertEquals($product->price_in_cents, $orderLine->product_price_in_cents);
            $this->assertEquals(1, $orderLine->quantity);
        }
    }

    #[Test]
    public function it_fails_with_an_invalid_token(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $paymentToken = PayBuddy::invalidToken();

        $response = $this->actingAs($user)
            ->postJson(route('order::checkout', [
                'payment_token' => $paymentToken,
                'products' => [
                    ['id' => $product->id, 'quantity' => 1]
                ]
            ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['payment_token']);
    }
}
