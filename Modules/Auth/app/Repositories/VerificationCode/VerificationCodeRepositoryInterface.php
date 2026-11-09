<?php

namespace Modules\Auth\Repositories\VerificationCode;

use Carbon\CarbonInterface;
use Modules\Auth\Enums\VerificationPurpose;
use Modules\Auth\Models\UserVerificationCode;

interface VerificationCodeRepositoryInterface
{
    /**
     * Get the latest verification code for a mobile number and purpose.
     */
    public function latestFor(string $identifier, VerificationPurpose $purpose): ?UserVerificationCode;

    /**
     * Atomically remove any existing code and store the new one.
     *
     * @param  array{
     *     identifier: string,
     *     purpose: VerificationPurpose,
     *     code: string,
     *     expires_at: CarbonInterface
     * }  $data
     */
    public function replaceFor(array $data): void;

    /**
     * Increase the failed-attempt counter of a verification code.
     */
    public function incrementAttempts(UserVerificationCode $verificationCode): void;

    /**
     * Delete a single verification code after it has been consumed.
     */
    public function deleteCode(UserVerificationCode $verificationCode): void;
}
