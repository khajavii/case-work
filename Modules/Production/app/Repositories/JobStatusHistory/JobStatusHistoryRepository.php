<?php

namespace Modules\Production\app\Repositories\JobStatusHistory;

use Modules\Production\Enums\JobStatus;
use Modules\Production\Models\JobStatusHistory;

class JobStatusHistoryRepository implements JobStatusHistoryRepositoryInterface
{
    public function __construct(protected JobStatusHistory $model)
    {

    }
    /**
     * {@inheritdoc}
     */
    public function create(
        int $jobId,
        int $changedBy,
        JobStatus $fromStatus,
        JobStatus $toStatus
    ): JobStatusHistory {

        /** @var JobStatusHistory $query */
        $query = $this->model->query();

        return $query->create([
            'job_id' => $jobId,
            'changed_by' => $changedBy,
            'from_status' => $fromStatus->value,
            'to_status' => $toStatus->value,
            'created_at' => now(),
        ]);
    }
}
