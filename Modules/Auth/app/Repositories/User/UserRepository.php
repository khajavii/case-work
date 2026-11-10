<?php

namespace Modules\Auth\Repositories\User;

use App\Models\User;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(protected User $model)
    {

    }
    /**
     * {@inheritdoc}
     */
    public function findByMobile(string $mobile): ?User
    {
        return $this->model->query()->where('mobile', $mobile)->first();
    }

    /**
     * {@inheritdoc}
     */
    public function findByEmail(string $email): ?User
    {
        return $this->model->query()->where('email', $email)->first();
    }

    /**
     * {@inheritdoc}
     */
    public function findOrCreateByMobile(string $mobile): User
    {
        // OTP-verified users are created with only their mobile marked as verified.
        return $this->model->query()->firstOrCreate(
            ['mobile' => $mobile],
            ['mobile_verified' => now()],
        );
    }
}
