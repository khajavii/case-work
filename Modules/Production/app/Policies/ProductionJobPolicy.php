<?php

namespace Modules\Production\Policies;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Production\Models\ProductionJob;

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

    /**
     * @param User $user
     * @param ProductionJob $job
     * @return bool
     */
    public function changeStatus(
        User $user,
        ProductionJob $job
    ): bool {
        if ($user->role === UserRole::INSTALLER) {
            return (int) $job->assigned_installer_id === (int) $user->id;
        }

        if (in_array($user->role, [
            UserRole::ADMIN,
            UserRole::PLANNER,
        ], true)) {
            return (int) $job->order->owner_id === (int) $user->id;
        }

        return false;
    }
}
