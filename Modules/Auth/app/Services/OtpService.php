<?php

namespace Modules\Auth\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Modules\Auth\Enums\VerificationPurpose;
use Modules\Auth\Repositories\User\UserRepositoryInterface;
use Modules\Auth\Repositories\VerificationCode\VerificationCodeRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class OtpService
{
    /** Number of minutes an OTP remains valid. */
    private const OTP_TTL_MINUTES = 2;

    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly VerificationCodeRepositoryInterface $verificationCodes,
    ) {}

    /**
     * Return the remaining time of an active code, or generate and store a new one.
     *
     * @return array{status: string, message: string, data: array{second: int}}
     */
    public function send(string $mobile): array
    {
        $existing = $this->verificationCodes->latestFor($mobile, VerificationPurpose::Login);

        if ($existing !== null && $existing->expires_at->isFuture()) {
            $seconds = max(1, (int) ceil(now()->diffInSeconds($existing->expires_at)));

            return [
                'status' => 'success',
                'message' => __('auth::messages.otp_cooldown', ['second' => $seconds]),
                'data' => ['second' => $seconds],
            ];
        }

        $code = (string) config('auth.otp.code');

        $this->verificationCodes->replaceFor([
            'identifier' => $mobile,
            'purpose' => VerificationPurpose::Login,
            'code' => Hash::make($code),
            'expires_at' => now()->addMinutes(self::OTP_TTL_MINUTES),
        ]);

        return [
            'status' => 'success',
            'message' => __('auth::messages.otp_sent'),
            'data' => ['second' => self::OTP_TTL_MINUTES * 60],
        ];
    }

    /**
     * Verify a submitted OTP and return the authenticated user.
     *
     * @throws ValidationException when the code is missing, expired, or incorrect
     * @throws HttpException when the attempt limit is exceeded
     */
    public function verify(string $mobile, string $code): User
    {
        $verificationCode = $this->verificationCodes->latestFor($mobile, VerificationPurpose::Login);

        if ($verificationCode === null || $verificationCode->expires_at->isPast()) {
            $this->failInvalidCode();
        }

        if ($verificationCode->hasExceededAttempts((int) config('auth.otp.max_attempts', 5))) {
            abort(429, __('auth::messages.too_many_attempts'));
        }

        if (! Hash::check($code, $verificationCode->code)) {
            $this->verificationCodes->incrementAttempts($verificationCode);

            $this->failInvalidCode();
        }

        $user = $this->users->findOrCreateByMobile($mobile);

        $this->verificationCodes->deleteCode($verificationCode);

        return $user;
    }

    private function failInvalidCode(): never
    {
        throw ValidationException::withMessages([
            'code' => __('auth::messages.invalid_code'),
        ]);
    }
}
