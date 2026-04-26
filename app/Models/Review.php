<?php

namespace App\Models;

use App\Traits\BelongsToBrand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $wedding_organizer_id
 * @property int|null $package_id
 * @property int $rating
 * @property string|null $comment
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Package|null $package
 * @property-read User $user
 * @property-read WeddingOrganizer $weddingOrganizer
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
 * @property int $weddingOrganizerId
 * @property int|null $packageId
 * @property Carbon|null $createdAt
 * @property Carbon|null $updatedAt
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Review whereWeddingOrganizerId($value)
 *
 * @mixin \Eloquent
 */
class Review extends Model
{
    use BelongsToBrand;

    protected $fillable = [
        'user_id',
        'wedding_organizer_id',
        'package_id',
        'rating',
        'comment',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
