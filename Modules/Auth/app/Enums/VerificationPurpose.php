<?php

namespace Modules\Auth\Enums;

/**
 * Identifies why a verification code was generated.
 */
enum VerificationPurpose: int
{
    case Login = 0;
    case ChangePassword = 1;
}
