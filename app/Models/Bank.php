<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $fillable = [
        'name',
        'code',
        'type',
        'account_number',
        'account_holder',
        'logo',
        'qris_payload',
        'qris_image',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
