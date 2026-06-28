<?php

use App\Http\Controllers\Api\AppSettingsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CBIRController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\FirebaseController;
use App\Http\Controllers\Api\FonnteWebhookController;
use App\Http\Controllers\Api\GeoController;
use App\Http\Controllers\Api\HistoryController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\LegalController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PackageController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\UserLanguageController;
use App\Http\Controllers\Api\VoucherController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\WorldRegionController;
use App\Http\Controllers\DatabaseProxyController;
use App\Http\Controllers\PusherAuthController;
use App\Models\User;
use App\Providers\NativeServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public: app config (app_name, owner_name, demo_video_url) — data dari backend, bukan template
Route::get('/settings', [AppSettingsController::class, 'index']);

// ── DIAGNOSTIC ENDPOINT — Test sinkronisasi mobile ──────────────────────────
// Akses: GET /api/ping dari mobile untuk verifikasi koneksi & data
Route::get('/ping', function () {
    $isMobile = NativeServiceProvider::isNativeMobile();
    $hostIp = NativeServiceProvider::mobileHostIp();

    $dbStatus = 'unknown';
    $userCount = 0;
    $dbError = null;

    try {
        $userCount = User::count();
        $dbStatus = 'connected';
    } catch (Throwable $e) {
        $dbStatus = 'error';
        $dbError = $e->getMessage();
    }

    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
        'is_mobile' => $isMobile,
        'host_ip' => $hostIp,
        'os' => PHP_OS_FAMILY,
        'db_driver' => config('database.default'),
        'db_status' => $dbStatus,
        'user_count' => $userCount,
        'db_error' => $dbError,
        'app_url' => config('app.url'),
        'locale' => app()->getLocale(),
        'php_version' => PHP_VERSION,
    ]);
});

// NativePHP Mobile DB Proxy — receives SQL queries from the Android/iOS app and executes them
// against the real MySQL database on the dev machine.
// ⚠️  Protected by X-DB-PROXY-SECRET header (must match APP_KEY).
Route::post('/db-proxy', [DatabaseProxyController::class, 'proxy']);

// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/auth/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/auth/clerk-sync', [AuthController::class, 'clerkSync']);

// Public endpoints
Route::get('/packages/public', [PackageController::class, 'index']);
Route::get('/products/public', [ProductController::class, 'index']);
Route::get('/legal/terms', [LegalController::class, 'getTerms']);
Route::get('/legal/privacy', [LegalController::class, 'getPrivacy']);
Route::get('/legal/about', [LegalController::class, 'getAbout']);
Route::get('/legal/help', [LegalController::class, 'getHelp']);

// Fonnte WhatsApp Webhooks (No auth required — verified by token in payload)
Route::post('/webhooks/fonnte', [FonnteWebhookController::class, 'handleIncomingMessage']);
Route::post('/webhooks/fonnte/connect', [FonnteWebhookController::class, 'handleConnectionStatus']);
Route::post('/webhooks/fonnte/status', [FonnteWebhookController::class, 'handleMessageStatus']);

// CBIR - AI Visual Search Public Probing
Route::get('/cbir/stats', [CBIRController::class, 'getStats']);
Route::get('/cbir/health', [CBIRController::class, 'healthCheck']);

// Regions (public — for cascading selects)
Route::get('/regions/provinces', [RegionController::class, 'provinces']);
Route::get('/regions/cities/{provinceCode}', [RegionController::class, 'cities']);
Route::get('/regions/districts/{cityCode}', [RegionController::class, 'districts']);
Route::get('/regions/villages/{districtCode}', [RegionController::class, 'villages']);

// World Regions (public — for all countries via countriesnow.space API)
Route::get('/world-regions/countries', [WorldRegionController::class, 'countries']);
Route::get('/world-regions/states', [WorldRegionController::class, 'states']);
Route::get('/world-regions/cities', [WorldRegionController::class, 'cities']);

// Geo (GeoNames — admin2 ≈ kecamatan, admin3 ≈ kelurahan, postal codes)
Route::get('/geo/admin2', [GeoController::class, 'admin2']);
Route::get('/geo/admin3', [GeoController::class, 'admin3']);
Route::get('/geo/postal-codes', [GeoController::class, 'postalCodes']);

// Firebase Status (public)
Route::get('/firebase/status', [FirebaseController::class, 'status']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::delete('/user/account', [AuthController::class, 'deleteAccount']);
    Route::get('/user', function (Request $request) {
        return response()->json([
            'status' => 'success',
            'data' => $request->user(),
        ]);
    });

    Route::post('/profile', [AuthController::class, 'updateProfile']);

    // Profile
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar']);
    Route::post('/profile/change-password', [ProfileController::class, 'changePassword']);
    Route::get('/profile/dashboard', [ProfileController::class, 'dashboard']);
    Route::put('/profile/nik', [ProfileController::class, 'updateNik']);
    Route::post('/profile/ktp-photo', [ProfileController::class, 'uploadKtp']);
    Route::post('/profile/selfie', [ProfileController::class, 'uploadSelfie']);
    Route::get('/profile/completion', [ProfileController::class, 'completion']);

    // Cart
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/add', [CartController::class, 'store']);
    Route::put('/cart/{cart}', [CartController::class, 'update']);
    Route::delete('/cart/{cart}', [CartController::class, 'destroy']);

    // Histories
    Route::get('/histories', [HistoryController::class, 'index']);

    // Home
    Route::get('/home', [HomeController::class, 'index']);

    // Categories
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);
    Route::get('/categories-with-packages', [CategoryController::class, 'withTopPackages']);

    // Vouchers
    Route::get('/vouchers', [VoucherController::class, 'index']);
    Route::post('/vouchers/{voucher}/claim', [VoucherController::class, 'claim']);

    // Packages
    Route::get('/packages', [PackageController::class, 'index']);
    Route::get('/packages/{id}', [PackageController::class, 'show']);
    Route::get('/packages/featured', [PackageController::class, 'featured']);
    Route::get('/packages/on-sale', [PackageController::class, 'onSale']);

    // Products
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::get('/products/featured', [ProductController::class, 'featured']);
    Route::get('/products/on-sale', [ProductController::class, 'onSale']);

    // Wishlist
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist/toggle', [WishlistController::class, 'toggle']);
    Route::get('/wishlist/{packageId}/check', [WishlistController::class, 'isInWishlist']);
    Route::post('/wishlist/bulk-add', [WishlistController::class, 'bulkAdd']);
    Route::delete('/wishlist/{packageId}', [WishlistController::class, 'removeFromWishlist']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

    // User Language
    Route::get('/user/language', [UserLanguageController::class, 'show']);
    Route::put('/user/language', [UserLanguageController::class, 'update']);

    // Search
    Route::get('/search', [SearchController::class, 'byText']);
    Route::post('/search/image', [SearchController::class, 'byImage']);

    // Chat
    Route::get('/messages/conversations', [ChatController::class, 'getConversations']);
    Route::get('/messages/conversations/{inboxId}', [ChatController::class, 'getMessages']);
    Route::get('/messages/unread-count', [ChatController::class, 'getUnreadCount']);
    Route::get('/messages/customers', [ChatController::class, 'getCustomersForChat']);
    Route::post('/messages/send', [ChatController::class, 'sendMessage']);
    Route::post('/messages/start', [ChatController::class, 'startConversation']);

    // Bookings / Orders
    Route::get('/bookings', [OrderController::class, 'getOrders']);
    Route::post('/bookings', [OrderController::class, 'createOrder']);
    Route::post('/bookings/{id}/pay', [OrderController::class, 'processPayment']);
    Route::get('/bookings/track/{orderNumber}', [OrderController::class, 'trackOrder']);
    Route::get('/bookings/{id}', [OrderController::class, 'show']);
    Route::post('/bookings/{id}/cancel', [OrderController::class, 'cancelOrder']);

    Route::get('/orders', [OrderController::class, 'getOrders']);
    Route::post('/orders', [OrderController::class, 'createOrder']);
    Route::post('/orders/{id}/pay', [OrderController::class, 'processPayment']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancelOrder']);

    // Reviews
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
    Route::get('/reviews/user', [ReviewController::class, 'getUserReviews']);
    Route::get('/reviews/package/{packageId}', [ReviewController::class, 'getPackageReviews']);
    Route::get('/reviews/organizer/{id}', [ReviewController::class, 'getOrganizerReviews']);

    // Wallet
    Route::get('/wallet', [WalletController::class, 'getWalletData']);
    Route::get('/wallet/history', [WalletController::class, 'getHistory']);

    // CBIR - AI Visual Search
    Route::post('/cbir/search', [CBIRController::class, 'searchSimilar']);
    Route::post('/cbir/index/product', [CBIRController::class, 'indexItem']);
    Route::post('/cbir/index/build', [CBIRController::class, 'buildIndex']);
    Route::get('/cbir/stats', [CBIRController::class, 'getStats']);
    Route::get('/cbir/health', [CBIRController::class, 'healthCheck']);

    // Firebase - Realtime Database Operations
    Route::prefix('firebase')->group(function (): void {
        Route::get('/status', [FirebaseController::class, 'status']);
        Route::post('/read', [FirebaseController::class, 'read']);
        Route::post('/write', [FirebaseController::class, 'write']);
        Route::post('/update', [FirebaseController::class, 'update']);
        Route::post('/delete', [FirebaseController::class, 'delete']);
        Route::post('/push', [FirebaseController::class, 'push']);
        Route::post('/children', [FirebaseController::class, 'children']);
        Route::post('/exists', [FirebaseController::class, 'exists']);
        Route::post('/sync-order', [FirebaseController::class, 'syncOrder']);
        Route::post('/sync-message', [FirebaseController::class, 'syncMessage']);
        Route::post('/clear-cache', [FirebaseController::class, 'clearCache']);
    });

    // Pusher private/presence auth endpoint (requires authenticated user)
    Route::post('/pusher/auth', [PusherAuthController::class, 'auth']);
});
