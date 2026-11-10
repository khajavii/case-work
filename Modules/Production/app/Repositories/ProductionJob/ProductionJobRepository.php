<?php

namespace Modules\Production\Repositories\ProductionJob;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Production\Models\ProductionJob;

class ProductionJobRepository implements ProductionJobRepositoryInterface
{
    public function __construct(protected ProductionJob $model)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function paginateVisibleTo(
        User $actor,
        array $filters
    ): LengthAwarePaginator {
        $query = $this->visibleTo($actor);

        return $query
            ->status($filters['status'] ?? null)
            ->orderId(
                isset($filters['order_id'])
                    ? (int) $filters['order_id']
                    : null
            )
            ->orderByDesc('id')
            ->paginate(
                perPage: (int) ($filters['per_page'] ?? config('general.pagination.default',15)),
                page: (int) ($filters['page'] ?? config('general.pagination.page', 1))
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
        /** @var ProductionJob $query */
        $query = $this->model->query();

        return $this->model->query()->create([
            'order_id' => $attributes['order_id'],
            'title' => $attributes['title'],
            'description' => $attributes['description'],
            'assigned_installer_id' => $attributes['assigned_installer_id'],
            'status' => $attributes['status'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function findVisibleToForUpdateOrFail(
        User $actor,
        int $jobId
    ): ProductionJob {

        /** @var ProductionJob $query */
        $query = $this->model->query();

        if ($actor->role === UserRole::INSTALLER) {
            $query->where('assigned_installer_id', $actor->id);
        } elseif (in_array($actor->role, [
            UserRole::ADMIN,
            UserRole::PLANNER,
        ], true)) {
            $query->whereHas('order', function (Builder $query) use ($actor): void {
                $query->where('owner_id', $actor->id);
            });
        } else {
            $query->whereRaw('1 = 0');
        }

        return $query
            ->whereKey($jobId)
            ->lockForUpdate()
            ->firstOrFail();
    }


    /**
     * Build a fail-closed visibility query.
     *
     * @param User $actor
     * @return Builder
     */
    private function visibleTo(User $actor): Builder
    {
        /** @var ProductionJob $query */
        $query = $this->model->query();

        $role = $actor->role instanceof UserRole
            ? $actor->role->value
            : (string) $actor->role;

        $userId = $actor->getAuthIdentifier();

        return match ($role) {
            UserRole::INSTALLER->value => $query->where(
                'assigned_installer_id',
                $userId
            ),

            UserRole::ADMIN->value,
            UserRole::PLANNER->value => $query->whereHas(
                'order',
                fn (Builder $orders) => $orders->where('owner_id', $userId)
            ),

            default => $query->whereRaw('1 = 0'),
        };
    }

}
