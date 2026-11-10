<?php

namespace Modules\Production\Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Production\Enums\JobStatus;
use Modules\Orders\Models\Order;
use Modules\Production\Models\JobStatusHistory;
use Modules\Production\Models\ProductionJob;

/**
 * Create or update demo jobs and their status histories.
 */
class ProductionDatabaseSeeder extends Seeder
{
    /**
     * Ordered stages used to build demo status histories.
     *
     * @var list<JobStatus>
     */
    private const STAGES = [
        JobStatus::CUTTING,
        JobStatus::EDGE_BANDING,
        JobStatus::ASSEMBLY,
        JobStatus::READY_FOR_INSTALLATION,
        JobStatus::INSTALLATION,
        JobStatus::COMPLETED,
    ];

    /**
     * Seed jobs and histories without using model relationships.
     */
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        $installerIds = [];

        foreach ([
                     'installer1@example.com',
                     'installer2@example.com',
                     'installer3@example.com',
                 ] as $email) {
            $installerIds[] = User::query()
                ->where('email', $email)
                ->where('role', UserRole::INSTALLER->value)
                ->firstOrFail()
                ->id;
        }

        // Target numbers are indexes in STAGES.
        $scenarios = [
            ['admin@example.com', 'Oliver Anderson', [0, 0, 0]],
            ['admin@example.com', 'Sophia Martin', [1, 2, 3]],
            ['planner1@example.com', 'Lucas Thompson', [3, 4, 5]],
            ['planner1@example.com', 'Charlotte White', [5, 5, 5]],
            ['planner2@example.com', 'Benjamin Harris', [0, 2, 4]],
            ['planner2@example.com', 'Amelia Clark', [5, 5, 5]],
        ];

        $templates = [
            [
                'title' => 'Kitchen Base Cabinets',
                'description' => 'White MDF carcasses with oak-effect doors and soft-close drawers.',
            ],
            [
                'title' => 'Kitchen Wall Cabinets',
                'description' => 'High-gloss white doors with adjustable shelves and soft-close hinges.',
            ],
            [
                'title' => 'Kitchen Island',
                'description' => 'Freestanding island with storage drawers and a breakfast bar.',
            ],
        ];

        ProductionJob::unguarded(function () use (
            $installerIds,
            $scenarios,
            $templates
        ): void {
            foreach ($scenarios as $scenarioIndex => $scenario) {
                [$ownerEmail, $customerName, $targets] = $scenario;

                $owner = User::query()
                    ->where('email', $ownerEmail)
                    ->firstOrFail();

                $order = Order::query()
                    ->where('owner_id', $owner->id)
                    ->where('customer_name', $customerName)
                    ->firstOrFail();

                foreach ($templates as $jobIndex => $template) {
                    $targetIndex = $targets[$jobIndex];

                    $installerId = $targetIndex === 0
                        ? null
                        : $installerIds[
                        ($scenarioIndex + $jobIndex) % count($installerIds)
                        ];

                    $job = ProductionJob::query()->updateOrCreate(
                        [
                            'order_id' => $order->id,
                            'title' => $template['title'],
                        ],
                        [
                            'description' => $template['description'],
                            'assigned_installer_id' => $installerId,
                            'status' => self::STAGES[$targetIndex]->value,
                        ]
                    );

                    // Use the job's creation time as a stable history anchor.
                    // All generated transitions remain in the past.
                    $historyStart = $job->created_at
                        ->copy()
                        ->subDays(count(self::STAGES));

                    for ($step = 1; $step <= $targetIndex; $step++) {
                        $changedBy = $step >= 4
                            ? $installerId
                            : $owner->id;

                        JobStatusHistory::query()->updateOrCreate(
                            [
                                'job_id' => $job->id,
                                'from_status' => self::STAGES[$step - 1]->value,
                                'to_status' => self::STAGES[$step]->value,
                            ],
                            [
                                'changed_by' => $changedBy,
                                'created_at' => $historyStart
                                    ->copy()
                                    ->addDays($step),
                            ]
                        );
                    }
                }
            }
        });
    }
}
