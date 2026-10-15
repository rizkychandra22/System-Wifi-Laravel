<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = "users";

    protected $fillable = [
        "name",
        "phone",
        "role",
        "password",
        "failed_login_attempts",
        "locked_until",
        "address",
        "device_id",
        "registered_by_id",
    ];

    protected $hidden = ["password", "remember_token"];

    protected function casts(): array
    {
        return [
            "password" => "hashed",
            "locked_until" => "datetime",
            "failed_login_attempts" => "integer",
        ];
    }

    // Spatie-style role checks
    public function hasRole(string|array $roles): bool
    {
        if (is_array($roles)) {
            return in_array($this->role, $roles, true);
        }

        return $this->role === $roles;
    }

    public function hasAnyRole(array|string ...$roles): bool
    {
        $rolesList = is_array($roles[0] ?? null) ? $roles[0] : $roles;
        return in_array($this->role, $rolesList, true);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole("admin");
    }

    public function isEmployee(): bool
    {
        return $this->hasRole("employee");
    }

    public function isCustomer(): bool
    {
        return $this->hasRole("customer");
    }

    // Relationships
    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, "registered_by_id");
    }

    public function registeredUsers(): HasMany
    {
        return $this->hasMany(User::class, "registered_by_id");
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class, "customer_id");
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, "customer_id");
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, "user_id");
    }

    public function overtimes(): HasMany
    {
        return $this->hasMany(Overtime::class, "user_id");
    }

    public function allowances(): HasMany
    {
        return $this->hasMany(Allowance::class, "user_id");
    }
}
