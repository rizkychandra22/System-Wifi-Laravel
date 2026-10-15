<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory;

    protected $table = "subscriptions";

    protected $fillable = [
        "customer_id",
        "wifi_package_id",
        "billing_day",
        "next_due_date",
        "status",
        "created_by_id",
    ];

    protected function casts(): array
    {
        return [
            "billing_day" => "integer",
            "next_due_date" => "datetime",
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, "customer_id");
    }

    public function wifiPackage(): BelongsTo
    {
        return $this->belongsTo(WifiPackage::class, "wifi_package_id");
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, "created_by_id");
    }
}
