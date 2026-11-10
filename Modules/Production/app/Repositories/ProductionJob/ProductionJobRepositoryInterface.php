<?php

namespace Modules\Production\app\Repositories\ProductionJob;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Production\Models\ProductionJob;

interface ProductionJobRepositoryInterface
{
    /**
     * Paginate jobs visible to the actor.
     *
     * @param array<string, mixed> $filters
     */
    public function paginateVisibleTo(
        User $actor,
        array $filters
    ): LengthAwarePaginator;

    /**
     * Find a visible job or throw a not-found exception.
     */
    public function findVisibleToOrFail(
        User $actor,
        int $jobId
    ): mixed;

    /**
     * Persist a job using trusted, server-built attributes.
     *
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes): ProductionJob;
}
