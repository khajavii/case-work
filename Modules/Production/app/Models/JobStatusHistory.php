<?php

namespace Modules\Production\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Modules\Orders\Enums\JobStatus;

/**
 * An audit record of a production job status transition.
 *
 * Records should be created by trusted application services and
 * treated as append-only. This model does not enforce immutability.
 *
 * @property int $id
 * @property int $job_id
 * @property int|null $changed_by
 * @property JobStatus $from_status
 * @property JobStatus $to_status
 * @property Carbon|null $created_at
 * @property-read ProductionJob $job
 * @property-read User|null $changedBy
 */
class JobStatusHistory extends Model
{
    /**
     * Disable the updated_at column, which is not present in the table.
     *
     * @var null
     */
    public const UPDATED_AT = null;

    /**
     * The associated database table.
     *
     * @var string
     */
    protected $table = 'job_status_histories';

    /**
     * Attributes that may be mass assigned by trusted application code.
     *
     * The actor and status values must not come from unchecked input.
     *
     * @var list<string>
     */
    protected $fillable = [
        'changed_by',
        'from_status',
        'to_status',
    ];

    /**
     * Get the attribute casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'from_status' => JobStatus::class,
            'to_status' => JobStatus::class,
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get the job whose status changed.
     *
     * @return BelongsTo<ProductionJob, $this>
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(ProductionJob::class, 'job_id');
    }

    /**
     * Get the user who performed the change.
     *
     * The user may be null after their account has been deleted.
     *
     * @return BelongsTo<User, $this>
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
