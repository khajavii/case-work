<?php

namespace Modules\Production\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Modules\Orders\Enums\JobStatus;
use Modules\Orders\Models\Order;

/**
 * A production and installation job belonging to an order.
 *
 * @property int $id
 * @property int $order_id
 * @property int|null $assigned_installer_id
 * @property string $title
 * @property string|null $description
 * @property JobStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Order $order
 * @property-read User|null $assignedInstaller
 * @property-read Collection<int, JobStatusHistory> $statusHistories
 */
class ProductionJob extends Model
{
    /**
     * The associated database table.
     *
     * @var string
     */
    protected $table = 'production_jobs';

    /**
     * Attributes that may be mass assigned.
     *
     * Status changes and installer assignments require authorization
     * and must be handled by application services.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
    ];

    /**
     * Default raw attribute values.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'cutting',
    ];

    /**
     * Get the attribute casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => JobStatus::class,
        ];
    }

    /**
     * Get the order containing this job.
     *
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * Get the installer assigned to this job, if any.
     *
     * @return BelongsTo<User, $this>
     */
    public function assignedInstaller(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'assigned_installer_id'
        );
    }

    /**
     * Get the status history in chronological order.
     *
     * The primary key provides stable ordering for matching timestamps.
     *
     * @return HasMany<JobStatusHistory, $this>
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(JobStatusHistory::class, 'job_id')
            ->orderBy('created_at')
            ->orderBy('id');
    }
}
