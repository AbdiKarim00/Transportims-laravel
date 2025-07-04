<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'personal_number',
        'first_name',
        'last_name',
        'phone',
        'department_id',
        'office_id',
        'status',
        'is_temporary_password',
        'last_login',
        'login_attempts',
        'locked_until',
        'deactivation_reason',
        'deactivation_date',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_temporary_password' => 'boolean',
        'last_login' => 'datetime',
        'locked_until' => 'datetime',
        'deactivation_date' => 'datetime',
    ];

    /**
     * Get the department that the user belongs to.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the office that the user belongs to.
     */
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Check if the user is locked.
     */
    public function isLocked()
    {
        return $this->locked_until !== null && $this->locked_until->isFuture();
    }

    /**
     * Check if the user is active.
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function hasRole($role)
    {
        // Check both the single role relationship and many-to-many roles
        if ($this->role && $this->role->name === $role) {
            return true;
        }

        return $this->roles()->where('name', $role)->exists();
    }

    public function hasPermission($permission)
    {
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permission) {
                $query->where('name', $permission);
            })
            ->exists();
    }

    public function preferences(): HasMany
    {
        return $this->hasMany(UserPreference::class);
    }

    public function loginAttempts(): HasMany
    {
        return $this->hasMany(UserLoginAttempt::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(UserActivityLog::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function driver(): HasOne
    {
        return $this->hasOne(Driver::class);
    }

    public function vehicle(): HasOne
    {
        return $this->hasOne(Vehicle::class, 'assigned_user_id');
    }

    public function fuelCards(): HasMany
    {
        return $this->hasMany(FuelCard::class, 'assigned_to');
    }

    public function assignedFuelCards(): HasMany
    {
        return $this->hasMany(FuelCard::class, 'assigned_by');
    }

    public function recordedTrips(): HasMany
    {
        return $this->hasMany(Trip::class, 'recorded_by');
    }

    public function recordedMaintenance(): HasMany
    {
        return $this->hasMany(MaintenanceRecord::class, 'recorded_by');
    }

    public function recordedInsurance(): HasMany
    {
        return $this->hasMany(InsurancePolicy::class, 'recorded_by');
    }

    public function recordedVehicleLogs(): HasMany
    {
        return $this->hasMany(VehicleLog::class, 'recorded_by');
    }

    public function uploadedDocuments(): HasMany
    {
        return $this->hasMany(VehicleDocument::class, 'uploaded_by');
    }

    public function uploadedMedia(): HasMany
    {
        return $this->hasMany(Media::class, 'uploaded_by');
    }

    public function history(): HasMany
    {
        return $this->hasMany(UserHistory::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(UserSession::class);
    }

    public function passwordHistory(): HasMany
    {
        return $this->hasMany(PasswordHistory::class);
    }

    public function passwordResets(): HasMany
    {
        return $this->hasMany(PasswordReset::class);
    }

    /**
     * Get the password for the user.
     */
    public function getAuthPassword()
    {
        return $this->password;
    }
}
