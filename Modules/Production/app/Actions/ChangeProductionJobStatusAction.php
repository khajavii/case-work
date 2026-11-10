<?php

namespace Modules\Production\app\Actions;

use App\Models\User;
use Illuminate\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Production\app\Repositories\JobStatusHistory\JobStatusHistoryRepositoryInterface;
use Modules\Production\app\Repositories\ProductionJob\ProductionJobRepositoryInterface;
use Modules\Production\Enums\JobStatus;
use Modules\Production\Models\JobStatusHistory;
use Modules\Production\Models\ProductionJob;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class ChangeProductionJobStatusAction
{
    public function __construct(
        private readonly ProductionJobRepositoryInterface $jobs,
        private readonly JobStatusHistoryRepositoryInterface $history,
    ) {}

    public function execute(
        User $actor,
        int $jobId,
        JobStatus $expectedStatus,
        JobStatus $nextStatus,
    ): ProductionJob
    {
        Gate::forUser($actor)->authorize(
            'viewAny',
            ProductionJob::class
        );

        return DB::transaction(function () use (
            $actor,
            $jobId,
            $expectedStatus,
            $nextStatus,
        ): ProductionJob {
            $job = $this->jobs->findVisibleToForUpdateOrFail(
                $actor,
                $jobId
            );

            Gate::forUser($actor)->authorize(
                'changeStatus',
                $job
            );

            $currentStatus = $job->status;

            if ($currentStatus !== $expectedStatus) {
                throw new ConflictHttpException(
                    __('general.errors.conflict_job_status')
                );
            }

            if (!$currentStatus->canTransitionTo($nextStatus)) {
                throw ValidationException::withMessages([
                    'status' => [
                        sprintf(
                            'Transition from "%s" to "%s" is not allowed.',
                            $currentStatus->value,
                            $nextStatus->value,
                        ),
                    ],
                ]);
            }

            $job->status = $nextStatus;
            $job->save();

            $this->history->create(
                $job->id,
                $actor->getAuthIdentifier(),
                $currentStatus,
                $nextStatus
            );

            return $job;
        }, 3);
    }
}
