<?php

namespace Modules\Production\Enums;
/**
 * Production and installation stages of a job.
 *
 * Enum casting does not enforce valid status transitions.
 */
enum JobStatus: string
{
    /**
     * Panels are being cut to size.
     */
    case CUTTING = 'cutting';

    /**
     * Panel edges are being finished.
     */
    case EDGE_BANDING = 'edge_banding';

    /**
     * Cabinet components are being assembled.
     */
    case ASSEMBLY = 'assembly';

    /**
     * The assembled job is ready for installation.
     */
    case READY_FOR_INSTALLATION = 'ready_for_installation';

    /**
     * The job is being installed at the customer location.
     */
    case INSTALLATION = 'installation';

    /**
     * Production and installation have been completed.
     */
    case COMPLETED = 'completed';

    /**
     * @param JobStatus $next
     * @return bool
     */
    public function canTransitionTo(self $next): bool
    {
        return match ($this->value) {
            'cutting' => $next->value === 'edge_banding',
            'edge_banding' => $next->value === 'assembly',
            'assembly' => $next->value === 'ready_for_installation',
            'ready_for_installation' => $next->value === 'installation',
            'installation' => $next->value === 'completed',
            'completed' => false,
        };
    }
}
