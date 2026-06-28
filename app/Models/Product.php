<?php

namespace App\Models;

use App\Providers\NativeServiceProvider;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('product_image')->singleFile();
        $this->addMediaCollection('videos');
    }

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'discount_price',
        'stock',
        'is_active',
        'is_featured',
        'features',
        'theme',
        'color',
        'min_capacity',
        'max_capacity',
    ];

    protected $casts = [
        'features' => 'array',
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'stock' => 'integer',
    ];

    protected $appends = [
        'image_url',
        'final_price',
        'is_wishlisted',
        'video_url',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name).'-'.Str::random(5);
            }
        });
    }

    public function getImageUrlAttribute()
    {
        $fallback = NativeServiceProvider::normalizeUrl(asset('images/placeholders/image-placeholder.png'));
        $url = $this->getFirstMediaUrl('product_image') ?: null;

        return $this->normalizeImageUrl($url, $fallback);
    }

    public function getVideoUrlAttribute(): ?string
    {
        $url = $this->getFirstMediaUrl('videos') ?: null;

        return $url ? NativeServiceProvider::normalizeUrl($url) : null;
    }

    public function getIsOutOfStockAttribute(): bool
    {
        return $this->stock <= 0;
    }

    public function getIsWishlistedAttribute(): bool
    {
        try {
            if (auth('sanctum')->check()) {
                return $this->wishlists()->where('user_id', auth('sanctum')->id())->exists();
            }
        } catch (\Throwable $e) {
        }

        try {
            if (class_exists(Filament::class) && Filament::auth()->check()) {
                return $this->wishlists()->where('user_id', Filament::auth()->id())->exists();
            }
        } catch (\Throwable $e) {
        }

        return false;
    }

    public function weddingFlowersDecorasi()
    {
        return $this->belongsTo(User::class, 'wedding_flowers_decorasi_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function getCategoryColorAttribute(): string
    {
        return $this->color ?? $this->category?->color ?? '#6366f1';
    }

    public function getFinalPriceAttribute(): float
    {
        return ($this->discount_price > 0) ? (float) $this->discount_price : (float) $this->price;
    }

    public function getBadgeStyleAttribute(): string
    {
        $color = $this->category_color;

        return "background: linear-gradient(135deg, {$color} 0%, {$color}cc 100%); 
                color: white; 
                box-shadow: 0 4px 12px {$color}40; 
                font-weight: 700; 
                text-transform: uppercase; 
                letter-spacing: 0.05em;
                padding: 4px 12px;
                border-radius: 99px;
                font-size: 0.7rem;
                border: none;";
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
            return NativeServiceProvider::normalizeUrl(url($url));
        }

        $resolved = asset('storage/'.ltrim($url, '/'));

        return NativeServiceProvider::normalizeUrl($resolved);
    }
}
