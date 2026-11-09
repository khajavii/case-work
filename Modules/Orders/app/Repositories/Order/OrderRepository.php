<?php

namespace Modules\Orders\Repositories\Order;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Auth\Models\UserVerificationCode;
use Modules\Orders\Enums\OrderStatus;
use Modules\Orders\Models\Order;

/**
 * Persist and query orders using Eloquent.
 */
class OrderRepository implements OrderRepositoryInterface
{
    public function __construct(protected Order $model)
    {

    }
    /**
     * Paginate owned orders with an optional status filter.
     */
    public function paginateForOwner(
        int $ownerId,
        ?OrderStatus $status,
        int $perPage,
        int $page
    ): LengthAwarePaginator {

        /** @var Order $query */
        $query = $this->model->query()
            ->where('owner_id', $ownerId);

        if ($status !== null) {
            $query->where('status', $status->value);
        }

        return $query
            ->orderByDesc('id')
            ->paginate(
                perPage: $perPage,
                page: $page
            );
    }

    /**
     * Persist a new order.
     *
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes): Order
    {
        return $this->model->query()->create($attributes);
    }
}
