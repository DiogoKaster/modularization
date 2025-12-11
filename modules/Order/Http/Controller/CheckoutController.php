<?php

namespace Modules\Order\Http\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Modules\Order\Http\Requests\CheckoutRequest;
use Modules\Order\Models\Order;
use Modules\Payment\PayBuddy;
use Modules\Product\CartItemCollection;
use Modules\Product\Warehouse\ProductStockManager;
use RuntimeException;

class CheckoutController
{
    public function __construct(
        protected ProductStockManager $productStockManager
    )
    {
    }

    public function __invoke(CheckoutRequest $request): JsonResponse
    {
        $cartItems = CartItemCollection::fromCheckoutData($request->input('products'));

        $orderTotalInCents = $cartItems->totalInCents();

        $payBuddy = PayBuddy::make();
        try {
            $charge = $payBuddy->charge($request->input('payment_token'), $orderTotalInCents, 'Modularization');
        } catch (RuntimeException) {
            throw ValidationException::withMessages(['payment_token' => 'The given payment token is not valid.']);
        }

        $order = Order::query()->create([
            'user_id' => $request->user()->id,
            'payment_id' => $charge['id'],
            'status' => 'paid',
            'payment_gateway' => 'PayBuddy',
            'total_in_cents' => $orderTotalInCents,
        ]);

        foreach ($cartItems->items as $cartItem) {
            $this->productStockManager->decrement($cartItem->product->id, $cartItem->quantity);

            $order->lines()->create([
                'product_id' => $cartItem->product->id,
                'product_price_in_cents' => $cartItem->product->priceInCents,
                'quantity' => $cartItem->quantity,
            ]);
        }

        return response()->json($order, 201);
    }
}
