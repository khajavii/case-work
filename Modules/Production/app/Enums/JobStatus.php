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
}
