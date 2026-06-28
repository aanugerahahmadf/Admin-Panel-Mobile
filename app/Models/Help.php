<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Help extends Model
{
    protected $table = 'helps';

    protected $fillable = [
        'title',
        'subtitle',
        'faqs',
        'contact_options',
    ];

    protected $casts = [
        'faqs' => 'array',
        'contact_options' => 'array',
    ];
}
