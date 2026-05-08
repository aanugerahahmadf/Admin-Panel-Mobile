<?php

namespace App\Models;

use App\Models\Traits\HasFilamentMessages;
use App\Traits\InteractsWithLanguages;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $full_name
 * @property string $email
 * @property float $balance
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $first_name
 * @property string|null $mid_name
 * @property string|null $last_name
 * @property string|null $username
 * @property string|null $avatar_url
 * @property string|null $phone
 * @property string|null $address
 * @property float|null $latitude
 * @property float|null $longitude
 * @property float|null $budget
 * @property Carbon|null $wedding_date
 * @property string|null $theme_preference
 * @property string|null $color_preference
 * @property string|null $event_concept
 * @property string|null $dream_venue
 * @property string|null $custom_fields
 * @property bool $active_status
 * @property string $avatar
 * @property int $dark_mode
 * @property string|null $messenger_color
 * @property-read UserLanguage|null $lang
 * @property-read mixed $name
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection<int, Order> $orders
 * @property-read int|null $orders_count
 * @property-read Collection<int, PaymentMethod> $paymentMethods
 * @property-read int|null $payment_methods_count
 * @property-read Collection<int, Payment> $payments
 * @property-read int|null $payments_count
 * @property-read Collection<int, Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read Collection<int, Role> $roles
 * @property-read int|null $roles_count
 * @property-read Collection<int, PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @property-read Collection<int, Wishlist> $wishlists
 * @property-read int|null $wishlists_count
 *
 * @method \Illuminate\Database\Eloquent\Builder allConversations()
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, bool $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, ?string $guard = null, bool $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereActiveStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAvatarUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereBudget($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereColorPreference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCustomFields($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDarkMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDreamVenue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEventConcept($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereMidName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereFullName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereMessengerColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereThemePreference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereWeddingDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, ?string $guard = null)
 * @method static \App\Models\User|null find(mixed $id, array|string $columns = ['*'])
 * @method static \App\Models\User findOrFail(mixed $id, array|string $columns = ['*'])
 * @method static \App\Models\User|null first(array|string $columns = ['*'])
 * @method static \App\Models\User firstOrFail(array|string $columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> get(array|string $columns = ['*'])
 *
 * @property string $fullName
 * @property Carbon|null $emailVerifiedAt
 * @property string|null $rememberToken
 * @property Carbon|null $createdAt
 * @property Carbon|null $updatedAt
 * @property string|null $firstName
 * @property string|null $midName
 * @property string|null $lastName
 * @property string|null $avatarUrl
 * @property Carbon|null $weddingDate
 * @property string|null $themePreference
 * @property string|null $colorPreference
 * @property string|null $eventConcept
 * @property string|null $dreamVenue
 * @property string|null $customFields
 * @property bool $activeStatus
 * @property int $darkMode
 * @property string|null $messengerColor
 * @property-read int|null $notificationsCount
 * @property-read bool|null $notificationsExists
 * @property-read int|null $ordersCount
 * @property-read bool|null $ordersExists
 * @property-read int|null $paymentMethodsCount
 * @property-read bool|null $paymentMethodsExists
 * @property-read int|null $paymentsCount
 * @property-read bool|null $paymentsExists
 * @property-read int|null $permissionsCount
 * @property-read bool|null $permissionsExists
 * @property-read int|null $rolesCount
 * @property-read bool|null $rolesExists
 * @property-read int|null $tokensCount
 * @property-read bool|null $tokensExists
 * @property-read int|null $wishlistsCount
 * @property-read bool|null $wishlistsExists
 *
 * @mixin \Eloquent
 */
class User extends Authenticatable implements FilamentUser, HasAvatar, HasName, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens;

    use HasFactory;
    use HasFilamentMessages;
    use HasRoles;
    use InteractsWithLanguages;
    use Notifiable;

    public function getFilamentName(): string
    {
        return $this->full_name ?? $this->username ?? 'User';
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return $this->hasRole('super_admin');
        }

        if ($panel->getId() === 'user') {
            return true;
        }

        return false;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'full_name',
        'first_name',
        'mid_name',
        'last_name',
        'username',
        'email',
        'password',
        'avatar_url',
        'phone',
        'whatsapp',
        'address',
        'latitude',
        'longitude',
        'budget',
        'wedding_date',
        'theme_preference',
        'color_preference',
        'event_concept',
        'dream_venue',
        'active_status',
        'gender',
        'social_id',
        'social_type',
    ];

    /**
     * The attributes that should be appended to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'avatar_url',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'wedding_date' => 'date',
            'budget' => 'decimal:2',
            'active_status' => 'boolean',
        ];
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * Fallback accessor for packages that expect 'name' attribute.
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->full_name,
        );
    }

    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                $path = $value ?: $this->avatar;

                if (! $path) {
                    return null;
                }

                if (filter_var($path, FILTER_VALIDATE_URL)) {
                    return $path;
                }

                $cleanPath = ltrim(str_replace('storage/', '', $path), '/');

                return asset('storage/'.$cleanPath);
            }
        );
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the user's withdrawals.
     */
    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function vouchers()
    {
        return $this->belongsToMany(Voucher::class, 'user_vouchers')
            ->withPivot('claimed_at', 'used_at', 'order_id')
            ->withTimestamps();
    }
}
