<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'config',
        'is_active',
    ];

    protected $casts = [
        'config' => 'json',
        'is_active' => 'boolean',
    ];
}
