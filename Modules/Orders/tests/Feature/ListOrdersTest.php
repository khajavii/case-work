<?php

namespace Modules\Orders\Tests\Feature;

use Modules\Orders\Tests\OrdersTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class ListOrdersTest extends OrdersTestCase
{
    public function test_guest_cannot_list_orders(): void
    {
        $this->getJson($this->ordersUrl())->assertUnauthorized();
    }

    public function test_installer_cannot_list_orders(): void
    {
        $this->signIn($this->createUser('installer'));
        $this->getJson($this->ordersUrl())->assertForbidden();
    }

    public static function managerRoles(): array
    {
        return ['admin' => ['admin'], 'planner' => ['planner']];
    }

    #[DataProvider('managerRoles')]
    public function test_manager_only_lists_owned_orders(string $role): void
    {
        $user = $this->createUser($role);
        $own = $this->createOrder($user);
        $this->createOrder($this->createUser('planner'));
        $this->signIn($user);

        $this->getJson($this->ordersUrl())->assertOk()
            ->assertJsonCount(1, 'data.result')
            ->assertJsonPath('data.result.0.id', $own->id)
            ->assertJsonPath('data.pagination.total', 1);
    }

    public function test_status_filter_preserves_ownership_scope(): void
    {
        $user = $this->createUser('planner');
        $matching = $this->createOrder($user, ['status' => 'in_progress']);
        $this->createOrder($user);
        $this->createOrder($this->createUser('planner'), ['status' => 'in_progress']);
        $this->signIn($user);

        $this->getJson($this->ordersUrl() . '?status=in_progress')->assertOk()
            ->assertJsonCount(1, 'data.result')
            ->assertJsonPath('data.result.0.id', $matching->id)
            ->assertJsonPath('data.pagination.total', 1);
    }

    public function test_pagination_returns_all_owned_orders_without_duplicates(): void
    {
        $user = $this->createUser('planner');
        $expected = [];
        for ($i = 0; $i < 5; $i++) {
            $expected[] = $this->createOrder($user)->id;
        }
        $this->createOrder($this->createUser('planner'));
        $this->signIn($user);
        $seen = [];

        foreach ([1 => 2, 2 => 2, 3 => 1] as $page => $count) {
            $response = $this->getJson($this->ordersUrl() . "?per_page=2&page={$page}")
                ->assertOk()->assertJsonCount($count, 'data.result')
                ->assertJsonPath('data.pagination.current_page', $page)
                ->assertJsonPath('data.pagination.per_page', 2)
                ->assertJsonPath('data.pagination.total', 5);
            $seen = array_merge($seen, array_column($response->json('data.result'), 'id'));
        }

        $this->assertCount(5, array_unique($seen));
        $this->assertEqualsCanonicalizing($expected, $seen);
    }

    public function test_invalid_filters_and_pagination_are_rejected(): void
    {
        $this->signIn($this->createUser('planner'));
        foreach ([['status' => 'invalid'], ['page' => 0], ['per_page' => 0], ['per_page' => 101]] as $query) {
            $this->getJson($this->ordersUrl() . '?' . http_build_query($query))
                ->assertUnprocessable()->assertJsonValidationErrors([array_key_first($query)]);
        }
    }
}
