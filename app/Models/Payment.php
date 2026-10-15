<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'payments';

    protected $fillable = [
        'customer_id',
        'wifi_package_id',
        'package_price',
        'ppn',
        'total_amount',
        'status',
        'payment_method',
        'invoice_number',
        'created_by_id',
    ];

    protected function casts(): array
    {
        return [
            'package_price' => 'decimal:2',
            'ppn' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function wifiPackage(): BelongsTo
    {
        return $this->belongsTo(WifiPackage::class, 'wifi_package_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}
