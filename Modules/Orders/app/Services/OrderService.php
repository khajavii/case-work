<?php

namespace Modules\Orders\Services;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;
use Modules\Orders\Enums\OrderStatus;
use Modules\Orders\Models\Order;
use Modules\Orders\Repositories\Order\OrderRepositoryInterface;

class OrderService
{
    public function __construct(
        protected OrderRepositoryInterface $orders
    ) {
    }

    /**
     * List orders owned by the authenticated user.
     *
     * @param array<string, mixed> $filters
     */
    public function paginate(
        User $actor,
        array $filters
    ): LengthAwarePaginator {
        Gate::forUser($actor)->authorize('viewAny', Order::class);

        $status = isset($filters['status'])
            ? OrderStatus::from($filters['status'])
            : null;

        return $this->orders->paginateForOwner(
            ownerId: (int) $actor->getAuthIdentifier(),
            status: $status,
            perPage: (int) ($filters['per_page'] ?? config('general.pagination.default', 15)),
            page: (int) ($filters['page'] ?? config('general.pagination.page', 1))
        );
    }

    /**
     * Create a pending order owned by the authenticated user.
     *
     * @param array<string, mixed> $data
     */
    public function create(User $actor, array $data): Order
    {
        Gate::forUser($actor)->authorize('create', Order::class);

        return $this->orders->create([
            'owner_id' => $actor->getAuthIdentifier(),
            'customer_name' => $data['customer_name'],
            'address' => $data['address'],
            'status' => OrderStatus::PENDING->value,
        ]);
    }
}
