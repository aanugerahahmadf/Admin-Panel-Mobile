<?php

namespace App\Providers;

use App\Database\MySqlProxyConnection;
use App\Filament\Admin\Auth\Login as AdminLogin;
use App\Filament\Admin\Auth\OtpEmailVerificationPrompt as AdminOtpEmailVerificationPrompt;
use App\Filament\Admin\Auth\OtpRequestPasswordReset as AdminOtpRequestPasswordReset;
use App\Filament\Admin\Auth\OtpResetPassword as AdminOtpResetPassword;
use App\Filament\Admin\Auth\Register as AdminRegister;
use App\Filament\Admin\Auth\VerifyOtp as AdminVerifyOtp;
use App\Filament\User\Auth\Login as UserLogin;
use App\Filament\User\Auth\OtpEmailVerificationPrompt as UserOtpEmailVerificationPrompt;
use App\Filament\User\Auth\OtpRequestPasswordReset as UserOtpRequestPasswordReset;
use App\Filament\User\Auth\OtpResetPassword as UserOtpResetPassword;
use App\Filament\User\Auth\Register as UserRegister;
use App\Filament\User\Auth\VerifyOtp as UserVerifyOtp;
use App\Livewire\BrowserSessionsComponent;
use App\Livewire\DeleteAccountComponent;
use App\Livewire\EditPasswordComponent;
use App\Livewire\Messages\Inbox;
use App\Livewire\Messages\Messages;
use App\Livewire\Messages\Search;
use App\Livewire\UsernameComponent;
use App\Models\User;
use App\Observers\MediaObserver;
use Filament\Actions\Exports\ExportColumn;
use Filament\Forms\Components\Field;
use Filament\Tables\Filters\BaseFilter;
use Filament\Infolists\Components\Entry;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Filament\Tables\Columns\Column;
use Filament\Tables\Table;
use Illuminate\Auth\Events\Login as LoginEvent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Spatie\Backup\BackupServiceProvider;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Filament\View\PanelsRenderHook;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\Blade;
use App\Models\Order;
use App\Models\Transaction;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // 🛠️ Development Shim for NativePHP Mobile
        // Prevents "Undefined function nativephp_call" when running on Windows/Desktop
        if (! function_exists('nativephp_call')) {
            require_once __DIR__.'/../../bootstrap/nativephp_shim.php';
        }

        // 🌉 Register MySQL Proxy Driver (For Mobile without pdo_mysql)
        $this->app->resolving('db', function ($db): void {
            $db->extend('mysql_proxy', function ($config, $name) {
                return new MySqlProxyConnection(
                    function () {
                        return new \stdClass;
                    }, // Fake PDO callback
                    $config['database'],
                    $config['prefix'],
                    $config
                );
            });
        });

        if (class_exists('ZipArchive')) {
            if (class_exists(BackupServiceProvider::class)) {
                $this->app->register(BackupServiceProvider::class);
            }
        }

        // ═══════════════════════════════════════════════════════════
        // FIX: filament-mobile-table compatibility with Filament v3/v4
        // Must be registered BEFORE other service providers boot so the
        // macros exist when FilamentMobileTableServiceProvider tries to use them.
        // ═══════════════════════════════════════════════════════════
        $this->app->booting(function (): void {
            // Use object property via array storage per-instance (PHP macros run bound to $this = Table instance)
            Table::macro('extraTableAttributes', function (array $attributes) {
                /** @var mixed $this */
                $key = '__mobileExtraAttrs';
                $existing = data_get((array) $this, $key, []);
                $merged = array_merge($existing, $attributes);
                // Store on the object via the Macroable mechanism
                $this->$key = $merged; // @phpstan-ignore property.notFound

                return $this;
            });
            Table::macro('getExtraTableAttributes', function () {
                /** @var mixed $this */
                $key = '__mobileExtraAttrs';

                return property_exists($this, $key) ? $this->$key : [];
            });
            Table::macro('extraAttributes', function (array $attributes) {
                /** @var mixed $this */
                $key = '__mobileExtraAttrs';
                $existing = data_get((array) $this, $key, []);
                $merged = array_merge($existing, $attributes);
                $this->$key = $merged; // @phpstan-ignore property.notFound

                return $this;
            });
            Table::macro('getExtraAttributes', function () {
                /** @var mixed $this */
                $key = '__mobileExtraAttrs';

                return property_exists($this, $key) ? $this->$key : [];
            });

            Table::macro('mobileCards', function (bool $condition = true) {
                /** @var mixed $this */
                return $this->extraTableAttributes(['mobile-cards' => $condition]);
            });

            Table::macro('mobileCardFeatured', function (string $column, string $color = 'primary') {
                /** @var mixed $this */
                return $this->extraTableAttributes([
                    'mobile-card-featured-column' => $column,
                    'mobile-card-featured-color' => $color,
                ]);
            });
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 🚀 Force HTTPS for Ngrok/Production Assets
        if (str_contains(config('app.url'), 'https://')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // 🚀 Vercel Read-Only Filesystem Fix
        if (env('VERCEL')) {
            $storagePath = '/tmp/storage';
            if (! is_dir($storagePath)) {
                @mkdir($storagePath, 0777, true);
                @mkdir($storagePath.'/framework/views', 0777, true);
                @mkdir($storagePath.'/framework/cache/data', 0777, true);
                @mkdir($storagePath.'/framework/cache/filament', 0777, true);
                @mkdir($storagePath.'/framework/sessions', 0777, true);
                @mkdir($storagePath.'/logs', 0777, true);
                @mkdir($storagePath.'/livewire-tmp', 0777, true);
            }
            config([
                'view.compiled' => $storagePath.'/framework/views',
                'cache.stores.file.path' => $storagePath.'/framework/cache/data',
                'session.files' => $storagePath.'/framework/sessions',
                'filament.cache_path' => $storagePath.'/framework/cache/filament',
                'livewire.temporary_file_upload.directory' => $storagePath.'/livewire-tmp',
            ]);
        }

        // ═══════════════════════════════════════════════════════════
        // PERSISTENT SESSION CONFIGURATION (WEB & MOBILE)
        // ═══════════════════════════════════════════════════════════
        // Pastikan session tidak pernah expired selama server hidup
        config([
            'session.expire_on_close' => false,
            'session.lottery' => [0, 100], // Matikan Garbage Collection (0% chance)
        ]);

        // Grant all permissions to super_admin role
        Gate::before(function ($user, $ability) {
            return $user->hasRole('super_admin') ? true : null;
        });

        // Automatically activate user on login (Filament/Web/API)
        Event::listen(
            LoginEvent::class,
            function ($event): void {
                $user = $event->user;
                if ($user instanceof User && ! $user->active_status) {
                    $user->update(['active_status' => true]);
                }
            }
        );

        Media::observe(MediaObserver::class);
        Order::observe(\App\Observers\OrderObserver::class);
        Transaction::observe(\App\Observers\TransactionObserver::class);
        Livewire::component('edit_password_form', EditPasswordComponent::class);
        Livewire::component('delete_account_form', DeleteAccountComponent::class);
        Livewire::component('browser_sessions_form', BrowserSessionsComponent::class);
        Livewire::component('fm-inbox', Inbox::class);
        Livewire::component('fm-messages', Messages::class);
        Livewire::component('fm-search', Search::class);
        Livewire::component('username-component', UsernameComponent::class);

        // 🔐 ADMIN PANEL — AUTH COMPONENTS
        Livewire::component('app.filament.admin.auth.login', AdminLogin::class);
        Livewire::component('app.filament.admin.auth.register', AdminRegister::class);
        Livewire::component('app.filament.admin.auth.otp-request-password-reset', AdminOtpRequestPasswordReset::class);
        Livewire::component('app.filament.admin.auth.otp-reset-password', AdminOtpResetPassword::class);
        Livewire::component('app.filament.admin.auth.verify-otp', AdminVerifyOtp::class);
        Livewire::component('app.filament.admin.auth.otp-email-verification-prompt', AdminOtpEmailVerificationPrompt::class);

        // 🔐 USER PANEL — AUTH COMPONENTS
        Livewire::component('app.filament.user.auth.login', UserLogin::class);
        Livewire::component('app.filament.user.auth.register', UserRegister::class);
        Livewire::component('app.filament.user.auth.otp-request-password-reset', UserOtpRequestPasswordReset::class);
        Livewire::component('app.filament.user.auth.otp-reset-password', UserOtpResetPassword::class);
        Livewire::component('app.filament.user.auth.verify-otp', UserVerifyOtp::class);
        Livewire::component('app.filament.user.auth.otp-email-verification-prompt', UserOtpEmailVerificationPrompt::class);

        Table::configureUsing(function (Table $table): void {
            $table->searchable();
        });

        // 🎯 GLOBAL ALIGNMENT CENTER UNTUK SEMUA TABLE & EXPORTER
        Column::configureUsing(function (Column $column): void {
            $column->alignCenter()
                ->label(fn () => __($column->getName()));
        });

        // 🎯 GLOBAL AUTO-TRANSLATE UNTUK SEMUA "ISI TABLE" (ROW DATA) PADA WEBDAN NATIVEPHP
        \Filament\Tables\Columns\TextColumn::configureUsing(function (\Filament\Tables\Columns\TextColumn $column): void {
            $column->formatStateUsing(function ($state, $record, \Filament\Tables\Columns\TextColumn $column) {
                // Jangan paksa terjemahan untuk password, token, url, atau email
                if (is_string($state) && !filter_var($state, FILTER_VALIDATE_EMAIL) && !str_contains($state, 'http')) {
                    // Deteksi jika hanya angka dan titik/koma (seperti harga/telepon), dilewati.
                    if (!preg_match('/^[0-9.,\-+() ]+$/', $state)) {
                        return __($state);
                    }
                }
                return $state;
             });
        });

        // 🌐 AUTO TRANSLATE ALL FORM FIELDS & FILTERS (safe: ensures string return)
        Field::configureUsing(function (Field $field): void {
            $field->label(function () use ($field): string {
                $original = $field->getName();
                $translated = __($original);
                return is_string($translated) ? $translated : $original;
            });
        });

        BaseFilter::configureUsing(function (BaseFilter $filter): void {
            $filter->translateLabel();
        });

        Entry::configureUsing(function (Entry $entry): void {
            $entry->label(function () use ($entry): string {
                $original = $entry->getName();
                $translated = __($original);
                return is_string($translated) ? $translated : $original;
            });
        });

        ExportColumn::configureUsing(function (ExportColumn $column): void {
            $column->formatStateUsing(function ($state) {
                if (is_string($state) && !filter_var($state, FILTER_VALIDATE_EMAIL) && !str_contains($state, 'http')) {
                    if (!preg_match('/^[0-9.,\-+() ]+$/', $state)) {
                        $state = __($state);
                    }
                }
                if ($state instanceof \UnitEnum) {
                    $state = $state instanceof \BackedEnum ? $state->value : $state->name;
                }
                
                // Ensure the return value is a string or null for Exporter compatibility
                return $state !== null ? (string) $state : null;
            });
        });

        // FilamentAsset::register([
        //     Css::make('app-stylesheet', Vite::asset('resources/css/app.css')),
        //     // Fallback: register mobile-cards CSS directly in case the vendor package's
        //     // service provider failed to register it due to the macro compatibility issue.
        //     // Css::make('mobile-cards-styles', base_path('vendor/slym758/filament-mobile-table/resources/css/mobile-cards.css')),
        // ]);

        // 💳 MIDTRANS SNAP MODAL INTEGRATION
        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_END,
            fn (): string => Blade::render('
                <div id="snap-container"></div>
                @include("filament.snap-script")
            '),
        );


        // Singletons are now registered in NativeServiceProvider
    }
}
