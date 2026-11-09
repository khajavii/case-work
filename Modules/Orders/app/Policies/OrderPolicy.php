<?php

namespace Modules\Orders\Policies;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     */
    public function __construct() {}

    /**
     * Determine whether the user may list owned orders.
     */
    public function viewAny(User $user): bool
    {
        return $this->canManageOrders($user);
    }

    /**
     * Determine whether the user may create an order.
     */
    public function create(User $user): bool
    {
        return $this->canManageOrders($user);
    }

    /**
     * Determine whether the user has an order management role.
     */
    private function canManageOrders(User $user): bool
    {
        $role = $user->role;

        if ($role instanceof UserRole) {
            $role = $role->value;
        }

        return in_array($role, [
            UserRole::ADMIN->value,
            UserRole::PLANNER->value,
        ], true);
    }
}
