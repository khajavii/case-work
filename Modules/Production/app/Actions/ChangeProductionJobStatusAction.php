<?php

namespace Modules\Production\app\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
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

    /**
     * Change a production job status and record the status transition.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws ConflictHttpException
     * @throws \Nette\Schema\ValidationException
     */
    public function execute(
        User $actor,
        int $jobId,
        JobStatus $expectedStatus,
        JobStatus $nextStatus,
    ): ProductionJob {
        $this->authorizeJobListing($actor);

        return DB::transaction(
            fn (): ProductionJob => $this->changeStatus(
                $actor,
                $jobId,
                $expectedStatus,
                $nextStatus,
            ),
            3,
        );
    }

    /**
     * Execute the status transition within the current transaction.
     *
     * @param User $actor
     * @param int $jobId
     * @param JobStatus $expectedStatus
     * @param JobStatus $nextStatus
     * @return ProductionJob
     */
    private function changeStatus(
        User $actor,
        int $jobId,
        JobStatus $expectedStatus,
        JobStatus $nextStatus,
    ): ProductionJob {
        $job = $this->jobs->findVisibleToForUpdateOrFail(
            $actor,
            $jobId,
        );

        $this->authorizeStatusChange($actor, $job);

        $currentStatus = $job->status;

        $this->ensureExpectedStatus(
            $currentStatus,
            $expectedStatus,
        );

        $this->ensureValidTransition(
            $currentStatus,
            $nextStatus,
        );

        $this->persistStatusChange(
            $job,
            $actor,
            $currentStatus,
            $nextStatus,
        );

        return $job;
    }


    /**
     * Ensure the actor is authorized to access the production job listing.
     *
     * @param User $actor
     * @return void
     */
    private function authorizeJobListing(User $actor): void
    {
        Gate::forUser($actor)->authorize(
            'viewAny',
            ProductionJob::class,
        );
    }


    /**
     * Ensure the actor is authorized to change the production job status.
     *
     * @param User $actor
     * @param ProductionJob $job
     * @return void
     */
    private function authorizeStatusChange(
        User $actor,
        ProductionJob $job,
    ): void {
        Gate::forUser($actor)->authorize(
            'changeStatus',
            $job,
        );
    }

    /**
     * Ensure the current status matches the expected status.
     *
     * @throws ConflictHttpException
     */
    private function ensureExpectedStatus(
        JobStatus $currentStatus,
        JobStatus $expectedStatus,
    ): void {
        if ($currentStatus === $expectedStatus) {
            return;
        }

        throw new ConflictHttpException(
            __('general.errors.conflict_job_status'),
        );
    }

    /**
     * Ensure the requested status transition is allowed.
     *
     * @throws ValidationException
     */
    private function ensureValidTransition(
        JobStatus $currentStatus,
        JobStatus $nextStatus,
    ): void {
        if ($currentStatus->canTransitionTo($nextStatus)) {
            return;
        }

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


    /**
     * Persist the new status and create its history record.
     *
     * @param ProductionJob $job
     * @param User $actor
     * @param JobStatus $currentStatus
     * @param JobStatus $nextStatus
     * @return void
     */
    private function persistStatusChange(
        ProductionJob $job,
        User $actor,
        JobStatus $currentStatus,
        JobStatus $nextStatus,
    ): void {
        $job->status = $nextStatus;
        $job->save();

        $this->history->create(
            $job->id,
            $actor->getAuthIdentifier(),
            $currentStatus,
            $nextStatus,
        );
    }
}
