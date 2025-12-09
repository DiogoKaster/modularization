<?php

namespace Modules\Order\Http\Controller;

use Illuminate\Validation\ValidationException;
use Modules\Order\Http\Requests\CheckoutRequest;
use Modules\Order\Models\Order;
use Modules\Payment\PayBuddy;
use Modules\Product\Models\Product;
use RuntimeException;

class CheckoutController
{
    public function __invoke(CheckoutRequest $request)
    {
        $inputData = $request->collect('products');

        $ids = $inputData->pluck('id');

        $foundProducts = Product::findMany($ids)->keyBy('id');

        $products = collect($request->input('products'))->map(function ($product) use ($foundProducts) {
            return [
                'product' => $foundProducts->get($product['id']),
                'quantity' => $product['quantity'],
            ];
        });

        $orderTotalInCents = $products->sum(fn($product) => $product['product']->price_in_cents * $product['quantity']);

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

        foreach ($products as $product) {
            $product['product']->decrement('stock', $product['quantity']);

            $order->lines()->create([
                'product_id' => $product['product']->id,
                'product_price_in_cents' => $product['product']->price_in_cents,
                'quantity' => $product['quantity'],
            ]);
        }

        return response()->json($order, 201);
    }
}
