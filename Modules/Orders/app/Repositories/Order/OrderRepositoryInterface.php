<?php

namespace Modules\Orders\Repositories\Order;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Orders\Enums\OrderStatus;
use Modules\Orders\Models\Order;

/**
 * Define persistence operations for orders.
 */
interface OrderRepositoryInterface
{
    /**
     * Paginate orders belonging to a specific owner.
     */
    public function paginateForOwner(
        int $ownerId,
        ?OrderStatus $status,
        int $perPage,
        int $page
    ): LengthAwarePaginator;

    /**
     * Persist a new order.
     *
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes): Order;
}
