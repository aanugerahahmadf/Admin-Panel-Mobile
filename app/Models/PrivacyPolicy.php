<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrivacyPolicy extends Model
{
    protected $table = 'privacy_policies'; // Explicit table name

    protected $fillable = ['title', 'content'];

    protected $casts = [
        'content' => 'array',
    ];
}
