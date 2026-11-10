<?php

namespace Modules\Production\Tests\Feature;

use Modules\Production\Tests\ProductionTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class ListProductionJobsTest extends ProductionTestCase
{
    public function test_guest_cannot_list_jobs(): void
    {
        $this->getJson($this->jobsUrl())->assertUnauthorized();
    }

    public function test_installer_only_lists_assigned_jobs(): void
    {
        $own = $this->createJob();
        $this->createJob(['assigned_installer_id' => $this->otherInstaller->id]);
        $this->createJob(['assigned_installer_id' => null]);
        $this->signIn($this->installer);
        $this->getJson($this->jobsUrl())->assertOk()
            ->assertJsonCount(1, 'data.result')
            ->assertJsonPath('data.result.0.id', $own->id)
            ->assertJsonPath('data.pagination.total', 1);
    }

    public static function managerRoles(): array
    {
        return ['admin' => ['admin'], 'planner' => ['planner']];
    }

    #[DataProvider('managerRoles')]
    public function test_manager_only_lists_jobs_of_owned_orders(string $role): void
    {
        $owner = $this->createUser($role);
        $order = $this->createOrder($owner);
        $own = $this->createJob(['order_id' => $order->id]);
        $this->createJob();
        $this->signIn($owner);
        $this->getJson($this->jobsUrl())->assertOk()
            ->assertJsonCount(1, 'data.result')
            ->assertJsonPath('data.result.0.id', $own->id)
            ->assertJsonPath('data.pagination.total', 1);
    }

    public function test_status_filter_preserves_installer_scope(): void
    {
        $matching = $this->createJob(['status' => 'assembly']);
        $this->createJob();
        $this->createJob(['status' => 'assembly', 'assigned_installer_id' => $this->otherInstaller->id]);
        $this->signIn($this->installer);
        $this->getJson($this->jobsUrl() . '?status=assembly')->assertOk()
            ->assertJsonCount(1, 'data.result')
            ->assertJsonPath('data.result.0.id', $matching->id)
            ->assertJsonPath('data.pagination.total', 1);
    }

    public function test_pagination_returns_all_assigned_jobs_without_duplicates(): void
    {
        $expected = [];
        for ($i = 0; $i < 5; $i++) {
            $expected[] = $this->createJob()->id;
        }
        $this->createJob(['assigned_installer_id' => $this->otherInstaller->id]);
        $this->signIn($this->installer);
        $seen = [];
        foreach ([1 => 2, 2 => 2, 3 => 1] as $page => $count) {
            $response = $this->getJson($this->jobsUrl() . "?per_page=2&page={$page}")
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
        $this->signIn($this->installer);
        foreach ([['status' => 'invalid'], ['page' => 0], ['per_page' => 0], ['per_page' => 101]] as $query) {
            $this->getJson($this->jobsUrl() . '?' . http_build_query($query))
                ->assertUnprocessable()->assertJsonValidationErrors([array_key_first($query)]);
        }
    }
}
