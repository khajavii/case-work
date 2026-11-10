<?php

namespace Modules\Production\Tests\Feature;

use Modules\Production\Tests\ProductionTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class ShowProductionJobTest extends ProductionTestCase
{
    public function test_guest_cannot_view_job(): void
    {
        $this->getJson($this->jobUrl($this->createJob()->id))->assertUnauthorized();
    }

    public function test_installer_can_view_assigned_job(): void
    {
        $job = $this->createJob();
        $this->signIn($this->installer);
        $this->getJson($this->jobUrl($job->id))->assertOk()
            ->assertJsonPath('data.id', $job->id)
            ->assertJsonPath('data.status', 'cutting');
    }

    public function test_installer_cannot_view_unassigned_or_foreign_job(): void
    {
        $this->signIn($this->installer);
        foreach ([null, $this->otherInstaller->id] as $assignee) {
            $job = $this->createJob(['assigned_installer_id' => $assignee]);
            $this->getJson($this->jobUrl($job->id))
                ->assertUnprocessable()->assertJsonValidationErrors(['job_id']);
        }
    }

    public function test_missing_job_returns_scoped_validation_error(): void
    {
        $this->signIn($this->installer);
        $this->getJson($this->jobUrl(999999))
            ->assertUnprocessable()->assertJsonValidationErrors(['job_id']);
    }

    public static function managerRoles(): array
    {
        return ['admin' => ['admin'], 'planner' => ['planner']];
    }

    #[DataProvider('managerRoles')]
    public function test_manager_can_view_owned_job_but_not_foreign_job(string $role): void
    {
        $owner = $this->createUser($role);
        $order = $this->createOrder($owner);
        $own = $this->createJob(['order_id' => $order->id]);
        $foreign = $this->createJob();
        $this->signIn($owner);
        $this->getJson($this->jobUrl($own->id))->assertOk()->assertJsonPath('data.id', $own->id);
        $this->getJson($this->jobUrl($foreign->id))
            ->assertUnprocessable()->assertJsonValidationErrors(['job_id']);
    }
}
