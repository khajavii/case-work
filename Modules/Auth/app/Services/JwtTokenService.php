<?php

namespace Modules\Auth\Services;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Tymon\JWTAuth\JWTGuard;

class JwtTokenService
{
    public function __construct(private readonly AuthFactory $auth) {}

    /**
     * @return array{access_token: string, token_type: string, expires_in: int}
     */
    public function issue(User $user): array
    {
        return $this->tokenPayload($this->guard()->login($user));
    }

    /**
     * Invalidate the JWT attached to the current request.
     */
    public function logout(): void
    {
        $this->guard()->logout();
    }

    /**
     * Get the user authenticated by the JWT in the current request.
     *
     * @throws AuthenticationException
     */
    public function currentUser(): User
    {
        $user = $this->guard()->user();

        if (! $user instanceof User) {
            throw new AuthenticationException(__('auth::messages.invalid_token'));
        }

        return $user;
    }

    private function guard(): JWTGuard
    {
        /** @var JWTGuard $guard */
        $guard = $this->auth->guard('api');

        return $guard;
    }

    /**
     * Build the public token response payload.
     *
     * @return array{access_token: string, token_type: string, expires_in: int}
     */
    private function tokenPayload(string $token): array
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard()->factory()->getTTL() * 60,
        ];
    }
}
