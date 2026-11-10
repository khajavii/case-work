<?php

namespace Modules\Production\Repositories\JobStatusHistory;

use Modules\Production\Enums\JobStatus;
use Modules\Production\Models\JobStatusHistory;

interface JobStatusHistoryRepositoryInterface
{
    /**
     * create job status history
     *
     * @param int $jobId
     * @param int $changedBy
     * @param JobStatus $fromStatus
     * @param JobStatus $toStatus
     * @return JobStatusHistory
     */
    public function create(
        int $jobId,
        int $changedBy,
        JobStatus $fromStatus,
        JobStatus $toStatus
    ): JobStatusHistory;
}
