<?php

namespace Modules\Auth\Strategies;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Modules\Auth\Contracts\LoginStrategy;
use Modules\Auth\Contracts\Repositories\UserRepositoryInterface;
use Modules\Auth\Enums\LoginMethod;

class PasswordLoginStrategy implements LoginStrategy
{
    public function __construct(private readonly UserRepositoryInterface $users) {}

    public function method(): LoginMethod
    {
        return LoginMethod::Password;
    }

    /**
     * @param  array{mobile: string, password: string}  $credentials
     */
    public function authenticate(array $credentials): User
    {
        $user = $this->users->findByMobile($credentials['mobile']);

        if (
            $user === null
            || $user->password === null
            || ! Hash::check($credentials['password'], $user->password)
        ) {
            throw ValidationException::withMessages([
                'password' => __('auth::messages.invalid_credentials'),
            ]);
        }

        return $user;
    }
}
