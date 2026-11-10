<?php

namespace Modules\Orders\Tests;

use Modules\Orders\Tests\Support\CreatesOrders;
use Tests\ApiFeatureTestCase;

abstract class OrdersTestCase extends ApiFeatureTestCase
{
    use CreatesOrders;

    protected function ordersUrl(): string
    {
        return '/api/v1/orders';
    }
}
