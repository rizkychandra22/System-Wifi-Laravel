<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceLockout extends Model
{
    use HasFactory;

    protected $table = 'device_lockouts';

    protected $fillable = [
        'device_id',
        'locked_until',
    ];

    protected function casts(): array
    {
        return [
            'locked_until' => 'datetime',
        ];
    }

    public static function isLocked(string $deviceId): bool
    {
        $lockout = self::where('device_id', $deviceId)->first();
        return $lockout && $lockout->locked_until && $lockout->locked_until->isFuture();
    }
}
