<?php

namespace Modules\Orders\Enums;

/**
 * Overall lifecycle status of an order.
 */
enum OrderStatus: string
{
    /**
     * The order is waiting for production to progress.
     */
    case PENDING = 'pending';

    /**
     * Work on the order is in progress.
     */
    case IN_PROGRESS = 'in_progress';

    /**
     * All required jobs have been completed.
     */
    case COMPLETED = 'completed';
}
