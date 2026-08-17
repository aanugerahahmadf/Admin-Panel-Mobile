<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $package_id
 * @property int $rating
 * @property string|null $comment
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Package|null $package
 * @property-read User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review wherePackageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereUserId($value)
 * @method static \App\Models\Review|null find(mixed $id, array|string $columns = ['*'])
 * @method static \App\Models\Review findOrFail(mixed $id, array|string $columns = ['*'])
 * @method static \App\Models\Review|null first(array|string $columns = ['*'])
 * @method static \App\Models\Review firstOrFail(array|string $columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Collection<int, \App\Models\Review> get(array|string $columns = ['*'])
 *
 * @property int $userId
 * @property int|null $packageId
 * @property Carbon|null $createdAt
 * @property Carbon|null $updatedAt
 *
 * @mixin \Eloquent
 */
class Review extends Model
{
    protected $appends = ['photo_url', 'photo_urls'];

    protected $fillable = [
        'user_id',
        'package_id',
        'product_id',
        'rating',
        'title',
        'comment',
        'photo',
        'photos',
    ];

    protected $casts = [
        'photos' => 'array',
    ];

    public function getPhotoUrlAttribute(): ?string
    {
        $urls = $this->getPhotoUrlsAttribute();

        return $urls[0] ?? null;
    }

    public function getPhotoUrlsAttribute(): array
    {
        $paths = $this->photos ?: [];

        // Backward compatibility: ulasan lama hanya punya kolom `photo`
        if (empty($paths) && $this->photo) {
            $paths = [$this->photo];
        }

        return array_values(array_filter(array_map(
            fn ($p) => $p ? asset('storage/'.$p) : null,
            (array) $paths
        )));
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
