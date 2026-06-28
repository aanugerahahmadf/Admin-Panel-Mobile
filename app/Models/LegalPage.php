<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegalPage extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'content',
    ];

    /**
     * Cast content to an array for easy structure access
     */
    protected $casts = [
        'content' => 'array',
    ];
}
