<?php

namespace Modules\Product;

use Illuminate\Support\Collection;
use Modules\Product\Models\Product;

class CartItemCollection
{
    /**
     *  @param Collection<CartItem> $items
     */
    public function __construct(
        public Collection $items
    )
    {
    }

    public static function fromCheckoutData(array $data): CartItemCollection
    {
        $inputData = collect($data);

        $ids = $inputData->pluck('id');

        $foundProducts = Product::findMany($ids)->keyBy('id');

        $items = collect($data)->map(function ($product) use ($foundProducts) {
            return new CartItem(
                ProductDto::fromEloquentModel($foundProducts->get($product['id'])),
                $product['quantity']
            );
        });

        return new self($items);
    }

    public function totalInCents(): int
    {
        return $this->items->sum(fn(CartItem $cartItem) => $cartItem->product->priceInCents * $cartItem->quantity);
    }

    /**
     *  @return Collection<CartItem>
     */
    public function items(): Collection
    {
        return $this->items;
    }
}
