<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class TermsOfService extends Model
{
    use HasTranslations;

    protected $table = 'terms_of_service';

    protected array $translatable = ['title', 'content'];

    protected $fillable = ['title', 'content', 'title_translations', 'content_translations'];

    protected $casts = [
        'content' => 'array',
        'title_translations' => 'json',
        'content_translations' => 'json',
    ];

    public function getTitleAttribute($value): ?string
    {
        return $this->trans('title') ?? $value;
    }

    public function getContentAttribute($value): mixed
    {
        $translated = $this->trans('content');
        return $translated ?? $value;
    }
}