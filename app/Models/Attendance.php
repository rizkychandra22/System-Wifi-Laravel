<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "attendances";

    protected $fillable = [
        "user_id",
        "date",
        "clock_in",
        "clock_out",
        "grade",
        "clock_in_lat",
        "clock_in_lng",
        "clock_out_lat",
        "clock_out_lng",
        "status",
        "notes",
    ];

    protected function casts(): array
    {
        return [
            "clock_in" => "datetime",
            "clock_out" => "datetime",
            "clock_in_lat" => "double",
            "clock_in_lng" => "double",
            "clock_out_lat" => "double",
            "clock_out_lng" => "double",
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, "user_id");
    }
}
