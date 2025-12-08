<?php

namespace Modules\Order\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Product\Models\Product;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence,
            'price' => $this->faker->numberBetween(100, 1000),
            'stock' => $this->faker->numberBetween(1, 10),
        ];
    }
}
