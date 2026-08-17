<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    protected $fillable = [
        'user_id',
        'store_name',
        'contact_person',
        'no_telp',
        'store_description',
        'logo',
        'is_active',
        'is_partner',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_partner' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function packages(): HasMany
    {
        return $this->hasMany(Package::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
