<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Modules\Production\Models\JobStatusHistory;
use Modules\Production\Models\ProductionJob;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * An authenticated user of the cabinet tracking system.
 *
 * @property int $id
 * @property string $name
 * @property string|null $mobile
 * @property string|null $email
 * @property Carbon|null $email_verified
 * @property Carbon|null $mobile_verified
 * @property string|null $password
 * @property UserRole $role
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Order> $orders
 * @property-read Collection<int, ProductionJob> $assignedJobs
 * @property-read Collection<int, JobStatusHistory> $statusChanges
 */
class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    /**
     * The associated database table.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * Attributes that may be mass assigned.
     *
     * Roles and verification timestamps must be assigned explicitly
     * by trusted application code.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'mobile',
        'email',
        'password',
    ];

    /**
     * Attributes excluded from array and JSON serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attribute casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => UserRole::class,
            'email_verified' => 'datetime',
            'mobile_verified' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the orders owned by this user.
     *
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'owner_id');
    }

    /**
     * Get the production jobs assigned to this installer.
     *
     * @return HasMany<ProductionJob, $this>
     */
    public function assignedJobs(): HasMany
    {
        return $this->hasMany(
            ProductionJob::class,
            'assigned_installer_id'
        );
    }

    /**
     * Get the job status changes performed by this user.
     *
     * @return HasMany<JobStatusHistory, $this>
     */
    public function statusChanges(): HasMany
    {
        return $this->hasMany(
            JobStatusHistory::class,
            'changed_by'
        );
    }

    /**
     * Determine whether the user has the admin role.
     *
     * This check does not replace resource authorization.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    /**
     * Determine whether the user has the planner role.
     *
     * @return bool
     */
    public function isPlanner(): bool
    {
        return $this->role === UserRole::PLANNER;
    }

    /**
     * Determine whether the user has the installer role.
     *
     * @return bool
     */
    public function isInstaller(): bool
    {
        return $this->role === UserRole::INSTALLER;
    }

    /**
     * Get the identifier stored in the JWT subject claim.
     *
     * @return mixed
     */
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /**
     * Get additional claims to include in the JWT.
     *
     * @return array<string, mixed>
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }
}
