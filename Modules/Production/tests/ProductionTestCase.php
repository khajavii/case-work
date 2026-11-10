<?php

namespace Modules\Production\Tests;

use Illuminate\Foundation\Auth\User;
use Modules\Orders\Models\Order;
use Modules\Orders\Tests\Support\CreatesOrders;
use Modules\Production\Models\ProductionJob;
use Tests\ApiFeatureTestCase;

abstract class ProductionTestCase extends ApiFeatureTestCase
{
    use CreatesOrders;

    protected User $planner;
    protected User $installer;
    protected User $otherInstaller;
    protected Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->planner = $this->createUser('planner');
        $this->installer = $this->createUser('installer');
        $this->otherInstaller = $this->createUser('installer');
        $this->order = $this->createOrder($this->planner);
    }

    protected function jobsUrl(): string
    {
        return '/api/v1/productions';
    }

    protected function jobUrl(int $id): string
    {
        return $this->jobsUrl() . '/' . $id;
    }

    protected function statusUrl(int $id): string
    {
        return '/api/v1/jobs/' . $id . '/status';
    }

    protected function historyTable(): string
    {
        return 'job_status_histories';
    }

    protected function createJob(array $attributes = []): ProductionJob
    {
        $job = new ProductionJob();

        $job->forceFill([
            'order_id' => $this->order->id,
            'assigned_installer_id' => $this->installer->id,
            'title' => 'Test Cabinet Job',
            'description' => 'Test production job.',
            'status' => 'cutting',
            ...$attributes,
        ])->save();

        return $job;
    }

    protected function jobPayload(array $attributes = []): array
    {
        return [
            'order_id' => $this->order->id,
            'assigned_installer_id' => $this->installer->id,
            'title' => 'New Cabinet Job',
            'description' => 'A production job created through the API.',
            ...$attributes,
        ];
    }

    protected function statusPayload(
        string $from = 'cutting',
        string $to = 'edge_banding'
    ): array {
        return [
            'expected_status' => $from,
            'status' => $to,
        ];
    }

    protected function assertJobStatus(
        ProductionJob $job,
        string $status
    ): void {
        $this->assertDatabaseHas($job->getTable(), [
            'id' => $job->id,
            'status' => $status,
        ]);
    }
}
