<?php

namespace Modules\Orders\Tests\Feature;

use Modules\Orders\Models\Order;
use Modules\Orders\Tests\OrdersTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class CreateOrderTest extends OrdersTestCase
{
    public function test_guest_cannot_create_an_order(): void
    {
        $this->postJson($this->ordersUrl(), $this->orderPayload())->assertUnauthorized();
        $this->assertDatabaseCount((new Order())->getTable(), 0);
    }

    public function test_installer_cannot_create_an_order(): void
    {
        $this->signIn($this->createUser('installer'));
        $this->postJson($this->ordersUrl(), $this->orderPayload())->assertForbidden();
        $this->assertDatabaseCount((new Order())->getTable(), 0);
    }

    public static function managerRoles(): array
    {
        return ['admin' => ['admin'], 'planner' => ['planner']];
    }

    #[DataProvider('managerRoles')]
    public function test_manager_can_create_an_owned_order(string $role): void
    {
        $user = $this->createUser($role);
        $this->signIn($user);
        $payload = $this->orderPayload();

        $response = $this->postJson($this->ordersUrl(), $payload)
            ->assertCreated()
            ->assertJsonPath('data.customer_name', $payload['customer_name'])
            ->assertJsonPath('data.address', $payload['address'])
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseCount((new Order())->getTable(), 1);
        $this->assertDatabaseHas((new Order())->getTable(), [
            'id' => $response->json('data.id'),
            'owner_id' => $user->id,
            ...$payload,
            'status' => 'pending',
        ]);
    }

    public static function invalidPayloads(): array
    {
        return [
            'missing customer' => [['address' => '100 Test Street'], 'customer_name'],
            'missing address' => [['customer_name' => 'Customer'], 'address'],
            'blank customer' => [['customer_name' => ' ', 'address' => 'Street'], 'customer_name'],
            'blank address' => [['customer_name' => 'Customer', 'address' => ' '], 'address'],
            'customer is not string' => [['customer_name' => [], 'address' => 'Street'], 'customer_name'],
        ];
    }

    #[DataProvider('invalidPayloads')]
    public function test_invalid_order_is_not_saved(array $payload, string $field): void
    {
        $this->signIn($this->createUser('planner'));
        $this->postJson($this->ordersUrl(), $payload)
            ->assertUnprocessable()->assertJsonValidationErrors([$field]);
        $this->assertDatabaseCount((new Order())->getTable(), 0);
    }

    public function test_client_cannot_supply_owner_or_status(): void
    {
        $other = $this->createUser('planner');
        $this->signIn($this->createUser('planner'));

        foreach (['owner_id' => $other->id, 'status' => 'completed'] as $field => $value) {
            $this->postJson($this->ordersUrl(), $this->orderPayload([$field => $value]))
                ->assertUnprocessable()->assertJsonValidationErrors([$field]);
        }

        $this->assertDatabaseCount((new Order())->getTable(), 0);
    }
}
