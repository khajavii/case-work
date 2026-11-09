<?php

namespace Modules\Auth\Strategies;

use App\Models\User;
use Modules\Auth\Contracts\LoginStrategy;
use Modules\Auth\Enums\LoginMethod;
use Modules\Auth\Services\OtpService;

class OtpLoginStrategy implements LoginStrategy
{
    public function __construct(private readonly OtpService $otpService) {}

    public function method(): LoginMethod
    {
        return LoginMethod::Otp;
    }

    /**
     * @param  array{mobile: string, code: string}  $credentials
     */
    public function authenticate(array $credentials): User
    {
        return $this->otpService->verify($credentials['mobile'], $credentials['code']);
    }
}
