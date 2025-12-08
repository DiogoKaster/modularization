<?php

namespace Models;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Modules\Product\Models\Product;
use Modules\Product\Tests\ProductTestCase;

class ProductTest extends ProductTestCase
{
    use DatabaseMigrations;

    public function test_it_creates_a_products(): void
    {
        $product = Product::factory()->create();

        $this->assertInstanceOf(Product::class, $product);
    }
}
