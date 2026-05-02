<?php

namespace App\Models;

use App\Providers\NativeServiceProvider;
use App\Traits\BelongsToBrand;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use HasFactory;
    use BelongsToBrand;
    use InteractsWithMedia;

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
                $product->slug = Str::slug($product->name).'-'.Str::random(5);
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
        $fallback = NativeServiceProvider::normalizeUrl(asset('images/placeholders/image-placeholder.png'));
        $url = $this->getFirstMediaUrl('product_image') ?: null;

        return $this->normalizeImageUrl($url, $fallback);
    }

    private function normalizeImageUrl(?string $url, string $fallback): string
    {
        if (! filled($url)) {
            return $fallback;
        }

        if (Str::startsWith($url, ['http://', 'https://', 'data:image'])) {
            return NativeServiceProvider::normalizeUrl($url);
        }

        if (Str::startsWith($url, '/')) {
            return $url;
        }

        return NativeServiceProvider::normalizeUrl(asset('storage/'.ltrim($url, '/')));
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
        // Prioritas: Sanctum (mobile API) dulu, baru Filament (web)
        try {
            if (auth('sanctum')->check()) {
                return $this->wishlists()->where('user_id', auth('sanctum')->id())->exists();
            }
        } catch (\Throwable $e) {
            // Silently fail
        }

        try {
            if (class_exists(Filament::class) && Filament::auth()->check()) {
                return $this->wishlists()->where('user_id', Filament::auth()->id())->exists();
            }
        } catch (\Throwable $e) {
            // Silently fail
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
