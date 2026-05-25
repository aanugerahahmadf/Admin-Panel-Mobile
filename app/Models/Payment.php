<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'payment_number',
        'amount',
        'admin_fee',
        'total_amount',
        'payment_method',
        'payment_gateway',
        'transaction_id',
        'status',
        'bank_name',
        'account_number',
        'account_holder',
        'payment_proof',
        'payment_url',
        'paid_at',
        'expired_at',
        'cancelled_at',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'admin_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'expired_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'metadata' => 'json',
        'status' => PaymentStatus::class,
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function methodDetails()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method', 'code');
    }
}
