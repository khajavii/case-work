<?php

namespace Modules\Production\app\Repositories\ProductionJob;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Production\Models\ProductionJob;

class ProductionJobRepository implements ProductionJobRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function paginateVisibleTo(
        User $actor,
        array $filters
    ): LengthAwarePaginator {
        $query = $this->visibleTo($actor);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['order_id'])) {
            $query->where('order_id', $filters['order_id']);
        }

        return $query
            ->orderByDesc('id')
            ->paginate(
                perPage: (int) ($filters['per_page'] ?? 15),
                page: (int) ($filters['page'] ?? 1)
            );
    }

    /**
     * {@inheritdoc}
     */
    public function findVisibleToOrFail(
        User $actor,
        int $jobId
    ): mixed {

        return $this->visibleTo($actor)
            ->whereKey($jobId)
            ->firstOrFail();

    }

    /**
     * {@inheritdoc}
     */
    public function create(array $attributes): ProductionJob
    {
        $job = new ProductionJob();

        $job->order_id = $attributes['order_id'];
        $job->title = $attributes['title'];
        $job->description = $attributes['description'];
        $job->assigned_installer_id = $attributes['assigned_installer_id'];
        $job->status = $attributes['status'];

        $job->save();

        return $job;
    }

    /**
     * Build a fail-closed visibility query.
     */
    private function visibleTo(User $actor): Builder
    {
        $query = ProductionJob::query();

        $role = $actor->role instanceof UserRole
            ? $actor->role->value
            : (string) $actor->role;

        if ($role === UserRole::INSTALLER->value) {
            return $query->where(
                'assigned_installer_id',
                $actor->getAuthIdentifier()
            );
        }

        if (in_array($role, [
            UserRole::ADMIN->value,
            UserRole::PLANNER->value,
        ], true)) {
            return $query->whereHas(
                'order',
                fn (Builder $orders) => $orders->where(
                    'owner_id',
                    $actor->getAuthIdentifier()
                )
            );
        }

        return $query->whereRaw('1 = 0');
    }
}
