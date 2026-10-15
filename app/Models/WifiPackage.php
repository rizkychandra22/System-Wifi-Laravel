<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WifiPackage extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "wifi_packages";

    protected $fillable = ["name", "price"];

    protected function casts(): array
    {
        return [
            "price" => "decimal:2",
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, "wifi_package_id");
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, "wifi_package_id");
    }
}
