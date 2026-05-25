<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $fillable = [
        'name',
        'code',
        'type',
        'logo',
        'qris_payload',
        'qris_image',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class);
    }
}
