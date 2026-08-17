<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferenceOption extends Model
{
    protected $fillable = [
        'type',
        'key',
        'label',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'label' => 'json',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function getLabelForLocale(string $locale = 'en'): string
    {
        $labels = $this->label;
        return $labels[$locale] ?? $labels['en'] ?? $this->key;
    }
}
