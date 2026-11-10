<?php

namespace Modules\Orders\Tests\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Modules\Orders\Models\Order;

trait CreatesOrders
{
    protected function createOrder(Authenticatable $owner, array $attributes = []): Order
    {
        $order = new Order();
        $order->forceFill([
            'owner_id' => $owner->getAuthIdentifier(),
            'customer_name' => 'Test Customer',
            'address' => '100 Test Street',
            'status' => 'pending',
            ...$attributes,
        ])->save();

        return $order;
    }

    protected function orderPayload(array $attributes = []): array
    {
        return [
            'customer_name' => 'New Test Customer',
            'address' => '200 Test Street',
            ...$attributes,
        ];
    }
}
