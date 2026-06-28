<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TermsOfService extends Model
{
    protected $table = 'terms_of_service';

    protected $fillable = ['title', 'content'];

    protected $casts = [
        'content' => 'array',
    ];
}
