<?php

namespace Modules\Product;

use Modules\Product\Models\Product;

readonly class ProductDto
{
    public function __construct(
        public int $id,
        public int $priceInCents,
        public int $unitsInStock
    )
    {
    }

    public static function fromEloquentModel(Product $product): self
    {
        return new self($product->id, $product->price_in_cents, $product->stock);
    }
}
