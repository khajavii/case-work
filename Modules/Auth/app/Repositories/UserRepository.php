<?php

namespace Modules\Auth\Repositories;

use App\Models\User;
use Modules\Auth\Contracts\Repositories\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    public function findByMobile(string $mobile): ?User
    {
        return User::query()->where('mobile', $mobile)->first();
    }

    public function findOrCreateByMobile(string $mobile): User
    {
        // OTP-verified users are created with only their mobile marked as verified.
        return User::query()->firstOrCreate(
            ['mobile' => $mobile],
            ['mobile_verified' => now()],
        );
    }
}
