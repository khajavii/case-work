<?php

namespace Modules\Production\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class LogProductionJobReadyForInstallation implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly int $jobId,
        public readonly ?int $installerId,
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Production job became ready for installation.', [
            'job_id' => $this->jobId,
            'assigned_installer_id' => $this->installerId,
        ]);
    }
}
