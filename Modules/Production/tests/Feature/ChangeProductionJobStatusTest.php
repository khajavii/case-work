<?php

namespace Modules\Production\Tests\Feature;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Modules\Production\Tests\ProductionTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;

class ChangeProductionJobStatusTest extends ProductionTestCase
{
    public function test_guest_cannot_change_status(): void
    {
        $job = $this->createJob();
        $this->patchJson($this->statusUrl($job->id), $this->statusPayload())->assertUnauthorized();
        $this->assertJobStatus($job, 'cutting');
        $this->assertDatabaseCount($this->historyTable(), 0);
    }

    public function test_installer_cannot_change_unassigned_or_foreign_job(): void
    {
        $this->signIn($this->installer);
        foreach ([null, $this->otherInstaller->id] as $assignee) {
            $job = $this->createJob(['assigned_installer_id' => $assignee]);
            $this->patchJson($this->statusUrl($job->id), $this->statusPayload())
                ->assertUnprocessable()->assertJsonValidationErrors(['job_id']);
            $this->assertJobStatus($job, 'cutting');
        }
        $this->assertDatabaseCount($this->historyTable(), 0);
    }

    public function test_missing_job_is_rejected_without_history(): void
    {
        $this->signIn($this->installer);
        $this->patchJson($this->statusUrl(999999), $this->statusPayload())
            ->assertUnprocessable()->assertJsonValidationErrors(['job_id']);
        $this->assertDatabaseCount($this->historyTable(), 0);
    }

    public function test_valid_transition_updates_job_and_records_actor_and_time(): void
    {
        $job = $this->createJob();
        $this->signIn($this->installer);
        $this->patchJson($this->statusUrl($job->id), $this->statusPayload())->assertOk()
            ->assertJsonPath('data.id', $job->id)
            ->assertJsonPath('data.status', 'edge_banding');
        $this->assertJobStatus($job, 'edge_banding');
        $this->assertDatabaseCount($this->historyTable(), 1);
        $this->assertDatabaseHas($this->historyTable(), [
            'job_id' => $job->id,
            'changed_by' => $this->installer->id,
            'from_status' => 'cutting',
            'to_status' => 'edge_banding',
        ]);
        $history = DB::table($this->historyTable())->where('job_id', $job->id)->first();
        $this->assertNotNull($history);
        $this->assertNotNull($history->created_at);
    }

    public function test_job_can_progress_through_all_stages(): void
    {
        $stages = ['cutting', 'edge_banding', 'assembly', 'ready_for_installation', 'installation', 'completed'];
        $job = $this->createJob();
        $this->signIn($this->installer);
        for ($i = 0; $i < count($stages) - 1; $i++) {
            $this->patchJson($this->statusUrl($job->id), $this->statusPayload($stages[$i], $stages[$i + 1]))
                ->assertOk()->assertJsonPath('data.status', $stages[$i + 1]);
            $this->assertJobStatus($job, $stages[$i + 1]);
            $this->assertDatabaseHas($this->historyTable(), [
                'job_id' => $job->id,
                'changed_by' => $this->installer->id,
                'from_status' => $stages[$i],
                'to_status' => $stages[$i + 1],
            ]);
        }
        $this->assertDatabaseCount($this->historyTable(), 5);
    }

    public static function invalidTransitions(): array
    {
        return [
            'skip stage' => ['cutting', 'assembly'],
            'go backwards' => ['assembly', 'cutting'],
            'same stage' => ['edge_banding', 'edge_banding'],
            'leave completed' => ['completed', 'installation'],
        ];
    }

    #[DataProvider('invalidTransitions')]
    public function test_invalid_transition_does_not_change_data(string $from, string $to): void
    {
        $job = $this->createJob(['status' => $from]);
        $this->signIn($this->installer);
        $this->patchJson($this->statusUrl($job->id), $this->statusPayload($from, $to))
            ->assertUnprocessable()->assertJsonValidationErrors(['status']);
        $this->assertJobStatus($job, $from);
        $this->assertDatabaseCount($this->historyTable(), 0);
    }

    public function test_status_fields_are_required_and_valid(): void
    {
        $job = $this->createJob();
        $this->signIn($this->installer);
        $cases = [
            [['status' => 'edge_banding'], 'expected_status'],
            [['expected_status' => 'cutting'], 'status'],
            [['status' => 'unknown', 'expected_status' => 'cutting'], 'status'],
            [['status' => 'edge_banding', 'expected_status' => 'unknown'], 'expected_status'],
        ];
        foreach ($cases as [$payload, $field]) {
            $this->patchJson($this->statusUrl($job->id), $payload)
                ->assertUnprocessable()->assertJsonValidationErrors([$field]);
        }
        $this->assertJobStatus($job, 'cutting');
        $this->assertDatabaseCount($this->historyTable(), 0);
    }

    public function test_stale_expected_status_returns_conflict(): void
    {
        $job = $this->createJob(['status' => 'edge_banding']);
        $this->signIn($this->installer);
        $this->patchJson($this->statusUrl($job->id), $this->statusPayload())->assertConflict();
        $this->assertJobStatus($job, 'edge_banding');
        $this->assertDatabaseCount($this->historyTable(), 0);
    }

    public function test_repeated_request_does_not_duplicate_history(): void
    {
        $job = $this->createJob();
        $this->signIn($this->installer);
        $this->patchJson($this->statusUrl($job->id), $this->statusPayload())->assertOk();
        $this->patchJson($this->statusUrl($job->id), $this->statusPayload())->assertConflict();
        $this->assertJobStatus($job, 'edge_banding');
        $this->assertDatabaseCount($this->historyTable(), 1);
    }

    public function test_client_cannot_forge_history_actor_or_timestamp(): void
    {
        $job = $this->createJob();
        $this->signIn($this->installer);
        foreach ([
            'changed_by' => $this->planner->id,
            'created_at' => '2020-01-01 00:00:00',
            'owner_id' => $this->planner->id,
        ] as $field => $value) {
            $this->patchJson($this->statusUrl($job->id), [
                ...$this->statusPayload(), $field => $value,
            ])->assertUnprocessable()->assertJsonValidationErrors([$field]);
        }
        $this->assertJobStatus($job, 'cutting');
        $this->assertDatabaseCount($this->historyTable(), 0);
    }

    public function test_body_job_id_cannot_override_route_job_id(): void
    {
        $foreign = $this->createJob(['assigned_installer_id' => $this->otherInstaller->id]);
        $own = $this->createJob();
        $this->signIn($this->installer);
        $this->patchJson($this->statusUrl($foreign->id), [
            ...$this->statusPayload(), 'job_id' => $own->id,
        ])->assertUnprocessable()->assertJsonValidationErrors(['job_id']);
        $this->assertJobStatus($foreign, 'cutting');
        $this->assertJobStatus($own, 'cutting');
        $this->assertDatabaseCount($this->historyTable(), 0);
    }

    public static function managerRoles(): array
    {
        return ['admin' => ['admin'], 'planner' => ['planner']];
    }

    #[DataProvider('managerRoles')]
    public function test_manager_can_change_owned_job_but_not_foreign_job(string $role): void
    {
        $owner = $this->createUser($role);
        $order = $this->createOrder($owner);
        $own = $this->createJob(['order_id' => $order->id]);
        $foreign = $this->createJob();
        $this->signIn($owner);
        $this->patchJson($this->statusUrl($own->id), $this->statusPayload())->assertOk();
        $this->patchJson($this->statusUrl($foreign->id), $this->statusPayload())
            ->assertUnprocessable()->assertJsonValidationErrors(['job_id']);
        $this->assertJobStatus($own, 'edge_banding');
        $this->assertJobStatus($foreign, 'cutting');
        $this->assertDatabaseCount($this->historyTable(), 1);
        $this->assertDatabaseHas($this->historyTable(), [
            'job_id' => $own->id, 'changed_by' => $owner->id,
            'from_status' => 'cutting', 'to_status' => 'edge_banding',
        ]);
    }

    public function test_history_failure_rolls_back_status_and_history(): void
    {
        $job = $this->createJob();
        $this->signIn($this->installer);
        $this->withoutExceptionHandling();
        $table = $this->historyTable();

        // Fail after the history INSERT executes, while still inside the transaction.
        DB::listen(static function (QueryExecuted $query) use ($table): void {
            $sql = strtolower(ltrim($query->sql));
            if (str_starts_with($sql, 'insert') && str_contains($sql, $table)) {
                throw new RuntimeException('Simulated history persistence failure.');
            }
        });

        try {
            $this->patchJson($this->statusUrl($job->id), $this->statusPayload());
            $this->fail('The simulated persistence failure was not propagated.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Simulated history persistence failure.', $exception->getMessage());
        }

        $this->assertJobStatus($job, 'cutting');
        $this->assertDatabaseCount($this->historyTable(), 0);
    }
}
