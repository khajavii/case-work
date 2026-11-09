<?php

namespace Modules\Orders\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Modules\Orders\Enums\OrderStatus;
use Modules\Production\Models\ProductionJob;

/**
 * A customer order containing production and installation jobs.
 *
 * @property int $id
 * @property int $owner_id
 * @property string $customer_name
 * @property string $address
 * @property OrderStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $owner
 * @property-read Collection<int, ProductionJob> $jobs
 */
class Order extends Model
{
    /**
     * The associated database table.
     *
     * @var string
     */
    protected $table = 'orders';

    /**
     * Attributes that may be mass assigned.
     *
     * Ownership and status must be controlled by application logic.
     *
     * @var list<string>
     */
    protected $fillable = [
        'owner_id',
        'customer_name',
        'address',
        'status',
    ];

    /**
     * Default raw attribute values.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'pending',
    ];

    /**
     * Get the attribute casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
        ];
    }

    /**
     * Get the user who owns the order.
     *
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the production jobs belonging to the order.
     *
     * @return HasMany<ProductionJob, $this>
     */
    public function jobs(): HasMany
    {
        return $this->hasMany(ProductionJob::class, 'order_id');
    }
}
