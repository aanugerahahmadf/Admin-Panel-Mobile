<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasTranslations;

    protected array $translatable = ['name', 'description'];

    protected $fillable = ['name', 'slug', 'type', 'icon', 'color', 'description', 'name_translations', 'description_translations'];

    protected $casts = [
        'name_translations' => 'json',
        'description_translations' => 'json',
    ];

    public function getNameAttribute($value): ?string
    {
        return $this->trans('name') ?? $value;
    }

    public function getDescriptionAttribute($value): ?string
    {
        return $this->trans('description') ?? $value;
    }

    public function categoryPackages()
    {
        return $this->hasMany(Package::class);
    }

    public function categoryProducts()
    {
        return $this->hasMany(Product::class);
    }

    public function scopeForPackages($query)
    {
        return $query->where('type', 'package');
    }

    public function scopeForProducts($query)
    {
        return $query->where('type', 'product');
    }
}
