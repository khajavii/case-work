<?php

namespace App\Enums;

/**
 * Supported user roles.
 */
enum UserRole: string
{
    /** Manages orders and production operations within the authorized scope. */
    case ADMIN = 'admin';

    /** Plans orders and coordinates production jobs. */
    case PLANNER = 'planner';

    /** Works on assigned installation jobs. */
    case INSTALLER = 'installer';
}
