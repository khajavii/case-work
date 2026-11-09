<?php

namespace Modules\Auth\Enums;

/**
 * Supported login methods.
 */
enum LoginMethod: string
{
    case Password = 'password';
    case Otp = 'otp';
}
