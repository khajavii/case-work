<?php

namespace Modules\Production\Policies;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductionJobPolicy
{
    /**
     * Determine whether the user may list accessible jobs.
     */
    public function viewAny(User $user): bool
    {
        return in_array($this->role($user), [
            UserRole::ADMIN->value,
            UserRole::PLANNER->value,
            UserRole::INSTALLER->value,
        ], true);
    }

    /**
     * Determine whether the user may create jobs.
     */
    public function create(User $user): bool
    {
        return in_array($this->role($user), [
            UserRole::ADMIN->value,
            UserRole::PLANNER->value,
        ], true);
    }

    /**
     * Normalize the role regardless of enum casting.
     */
    private function role(User $user): string
    {
        return $user->role instanceof UserRole
            ? $user->role->value
            : (string) $user->role;
    }
}
