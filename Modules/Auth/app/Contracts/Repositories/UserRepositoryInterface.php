<?php

namespace Modules\Auth\Contracts\Repositories;

use App\Models\User;

interface UserRepositoryInterface
{
    /**
     * Find a user by their mobile number.
     */
    public function findByMobile(string $mobile): ?User;

    /**
     * Find a user by their email number.
     */
    public function findByEmail(string $email): ?User;

    /**
     * Find an existing user or create a mobile-verified user.
     */
    public function findOrCreateByMobile(string $mobile): User;
}
