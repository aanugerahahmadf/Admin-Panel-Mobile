<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeddingDecorationPolicy extends Model
{
    protected $fillable = ['title', 'content'];

    protected $casts = [
        'content' => 'array',
    ];
}
