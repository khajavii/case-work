<?php

namespace Modules\Production\Tests\Feature;

use Modules\Production\Models\ProductionJob;
use Modules\Production\Tests\ProductionTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class CreateProductionJobTest extends ProductionTestCase
{
    protected function createPayload(array $attributes = []): array
    {
        return $this->jobPayload($attributes);
    }

    protected function assertNoJobsWereCreated(): void
    {
        $this->assertDatabaseCount(
            (new ProductionJob())->getTable(),
            0
        );

        $this->assertDatabaseCount($this->historyTable(), 0);
    }

    public function test_guest_cannot_create_job(): void
    {
        $this->postJson(
            $this->jobsUrl(),
            $this->createPayload()
        )->assertUnauthorized();

        $this->assertNoJobsWereCreated();
    }

    public function test_installer_cannot_create_job(): void
    {
        $this->signIn($this->installer);

        $this->postJson(
            $this->jobsUrl(),
            $this->createPayload()
        )->assertForbidden();

        $this->assertNoJobsWereCreated();
    }

    public static function managerRoles(): array
    {
        return [
            'admin' => ['admin'],
            'planner' => ['planner'],
        ];
    }

    #[DataProvider('managerRoles')]
    public function test_manager_can_create_job_for_owned_order(
        string $role
    ): void {
        $owner = $this->createUser($role);
        $order = $this->createOrder($owner);

        $this->signIn($owner);

        $payload = $this->createPayload([
            'order_id' => $order->id,
        ]);

        $response = $this->postJson(
            $this->jobsUrl(),
            $payload
        )
            ->assertCreated()
            ->assertJsonPath('data.status', 'cutting')
            ->assertJsonPath('data.title', $payload['title']);

        $this->assertDatabaseCount(
            (new ProductionJob())->getTable(),
            1
        );

        $this->assertDatabaseHas(
            (new ProductionJob())->getTable(),
            [
                'id' => $response->json('data.id'),
                'order_id' => $order->id,
                'assigned_installer_id' => $payload['assigned_installer_id'],
                'title' => $payload['title'],
                'description' => $payload['description'],
                'status' => 'cutting',
            ]
        );

        $this->assertDatabaseCount($this->historyTable(), 0);
    }

    public function test_job_can_be_created_without_assigned_installer(): void
    {
        $this->signIn($this->planner);

        $payload = $this->createPayload([
            'assigned_installer_id' => null,
        ]);

        $response = $this->postJson(
            $this->jobsUrl(),
            $payload
        )
            ->assertCreated()
            ->assertJsonPath('data.status', 'cutting');

        $this->assertDatabaseCount(
            (new ProductionJob())->getTable(),
            1
        );

        $this->assertDatabaseHas(
            (new ProductionJob())->getTable(),
            [
                'id' => $response->json('data.id'),
                'order_id' => $this->order->id,
                'assigned_installer_id' => null,
                'status' => 'cutting',
            ]
        );

        $this->assertDatabaseCount($this->historyTable(), 0);
    }

    public function test_job_cannot_be_created_for_another_owners_order(): void
    {
        $foreignOrder = $this->createOrder(
            $this->createUser('planner')
        );

        $this->signIn($this->planner);

        $this->postJson(
            $this->jobsUrl(),
            $this->createPayload([
                'order_id' => $foreignOrder->id,
            ])
        )->assertNotFound();

        $this->assertNoJobsWereCreated();
    }

    public function test_job_cannot_be_created_for_missing_order(): void
    {
        $missingId = ((int) $this->order->newQuery()->max('id')) + 1000;

        $this->signIn($this->planner);

        $this->postJson(
            $this->jobsUrl(),
            $this->createPayload([
                'order_id' => $missingId,
            ])
        )->assertNotFound();

        $this->assertNoJobsWereCreated();
    }

    public function test_assignee_must_exist_and_have_installer_role(): void
    {
        $admin = $this->createUser('admin');

        $missingUserId = ((int) $this->planner->newQuery()->max('id')) + 1000;

        $this->signIn($this->planner);

        foreach ([
                     $this->planner->id,
                     $admin->id,
                     $missingUserId,
                 ] as $assigneeId) {
            $this->postJson(
                $this->jobsUrl(),
                $this->createPayload([
                    'assigned_installer_id' => $assigneeId,
                ])
            )
                ->assertUnprocessable()
                ->assertJsonValidationErrors([
                    'assigned_installer_id',
                ]);
        }

        $this->assertNoJobsWereCreated();
    }

    public function test_required_job_fields_are_validated(): void
    {
        $this->signIn($this->planner);

        $payload = $this->createPayload();

        unset($payload['title']);

        $this->postJson(
            $this->jobsUrl(),
            $payload
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title']);

        $this->postJson(
            $this->jobsUrl(),
            $this->createPayload([
                'title' => str_repeat('a', 201),
            ])
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title']);

        $this->assertNoJobsWereCreated();
    }

    public function test_client_cannot_supply_initial_status_or_owner(): void
    {
        $this->signIn($this->planner);

        foreach ([
                     'status' => 'completed',
                     'owner_id' => $this->planner->id,
                 ] as $field => $value) {
            $this->postJson(
                $this->jobsUrl(),
                $this->createPayload([
                    $field => $value,
                ])
            )
                ->assertUnprocessable()
                ->assertJsonValidationErrors([$field]);
        }

        $this->assertNoJobsWereCreated();
    }
}
