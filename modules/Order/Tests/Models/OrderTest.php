<?php

namespace Models;

use Modules\Order\Models\Order;
use Modules\Order\Tests\OrderTestCase;

class OrderTest extends OrderTestCase
{
    public function test_it_should_create_an_order(): void
    {
        $order = new Order();

        $this->assertInstanceOf(Order::class, $order);
    }
}
