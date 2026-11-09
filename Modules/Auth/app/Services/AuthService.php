<?php

namespace Modules\Auth\Services;

use App\Models\User;
use Modules\Auth\Contracts\LoginStrategy;

class AuthService
{
    public function __construct(
        private readonly LoginStrategy $strategy,
        private readonly JwtTokenService $tokens,
    ) {}

    /**
     * @param  array<string, mixed>  $credentials
     * @return array{access_token: string, token_type: string, expires_in: int}
     */
    public function login(array $credentials): array
    {
        $user = $this->strategy->authenticate($credentials);

        return $this->tokens->issue($user);
    }

    /**
     * Invalidate the JWT attached to the current request.
     */
    public function logout(): void
    {
        $this->tokens->logout();
    }

    /**
     * Get the user authenticated by the current request.
     */
    public function currentUser(): User
    {
        return $this->tokens->currentUser();
    }
}
