<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use InteractsWithMedia;
    use \App\Traits\BelongsToBrand;

    protected $fillable = [
        'wedding_organizer_id',
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'discount_price',
        'stock',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'is_active' => 'boolean',
        'stock' => 'integer',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('product_image')->singleFile();
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name) . '-' . Str::random(5);
            }
        });
    }

    public function weddingOrganizer()
    {
        return $this->belongsTo(WeddingOrganizer::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getImageUrlAttribute()
    {
        $fallback = asset('images/placeholders/image-placeholder.png');
        $url = $this->getFirstMediaUrl('product_image') ?: null;
        
        return $this->normalizeImageUrl($url, $fallback);
    }

    private function normalizeImageUrl(?string $url, string $fallback): string
    {
        if (! filled($url)) {
            return $fallback;
        }

        if (Str::startsWith($url, ['http://', 'https://', 'data:image'])) {
            return $url;
        }

        if (Str::startsWith($url, '/')) {
            return $url;
        }

        return asset('storage/' . ltrim($url, '/'));
    }

    public function getFinalPriceAttribute()
    {
        return $this->discount_price > 0 ? $this->discount_price : $this->price;
    }

    protected $appends = [
        'image_url',
        'final_price',
        'is_wishlisted',
    ];

    public function getIsWishlistedAttribute(): bool
    {
        // Try Filament (Web/Native)
        if (class_exists(\Filament\Facades\Filament::class) && \Filament\Facades\Filament::auth()->check()) {
            return $this->wishlists()->where('user_id', \Filament\Facades\Filament::auth()->id())->exists();
        }

        // Try Sanctum (Mobile API)
        if (auth('sanctum')->check()) {
            return $this->wishlists()->where('user_id', auth('sanctum')->id())->exists();
        }

        return false;
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
