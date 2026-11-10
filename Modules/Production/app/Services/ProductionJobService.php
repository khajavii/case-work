<?php

namespace Modules\Production\app\Services;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Modules\Orders\Models\Order;
use Modules\Production\app\Repositories\ProductionJob\ProductionJobRepositoryInterface;
use Modules\Production\Enums\JobStatus;
use Modules\Production\Models\ProductionJob;

class ProductionJobService
{
    public function __construct(
        protected ProductionJobRepositoryInterface $ProductionJobRepository
    ) {
    }

    /**
     * Paginate jobs accessible to the actor.
     *
     * @param array<string, mixed> $filters
     */
    public function paginate(
        User $actor,
        array $filters
    ): LengthAwarePaginator {
        Gate::forUser($actor)->authorize(
            'viewAny',
            ProductionJob::class
        );

        return $this->ProductionJobRepository->paginateVisibleTo($actor, $filters);
    }

    /**
     * Return one accessible job.
     */
    public function show(User $actor, int $jobId): ProductionJob
    {
        Gate::forUser($actor)->authorize(
            'viewAny',
            ProductionJob::class
        );

        return $this->ProductionJobRepository->findVisibleToOrFail($actor, $jobId);
    }

    /**
     * Create a cutting-stage job under an owned order.
     *
     * @param array<string, mixed> $data
     */
    public function create(User $actor, array $data): ProductionJob
    {
        Gate::forUser($actor)->authorize(
            'create',
            ProductionJob::class
        );

        return DB::transaction(function () use ($actor, $data) {
            $order = Order::query()
                ->whereKey($data['order_id'])
                ->where('owner_id', $actor->getAuthIdentifier())
                ->lockForUpdate()
                ->firstOrFail();

            $installerId = $data['assigned_installer_id'] ?? null;

            if ($installerId !== null) {
                $installer = User::query()
                    ->whereKey($installerId)
                    ->where('role', UserRole::INSTALLER->value)
                    ->sharedLock()
                    ->first();

                if ($installer === null) {
                    throw ValidationException::withMessages([
                        'assigned_installer_id' => [
                            'The selected user must be an installer.',
                        ],
                    ]);
                }

                $installerId = $installer->getKey();
            }

            return $this->ProductionJobRepository->create([
                'order_id' => $order->getKey(),
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'assigned_installer_id' => $installerId,
                'status' => JobStatus::CUTTING,
            ]);
        });
    }
}
