<?php

use App\Http\Controllers\Api\AppLockController;
use App\Http\Controllers\Api\AppSettingsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CBIRController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\FirebaseController;
use App\Http\Controllers\Api\FonnteWebhookController;
use App\Http\Controllers\Api\BriVaWebhookController;
use App\Http\Controllers\Api\PaymentWebhookController;
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
use App\Http\Controllers\Api\VendorController;
use App\Http\Controllers\Api\VoucherController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\WorldRegionController;
use App\Http\Controllers\DatabaseProxyController;
use App\Http\Controllers\PusherAuthController;
use App\Models\User;
use App\Providers\NativeServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
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
if (App::environment('local', 'testing')) {
    Route::post('/db-proxy', [DatabaseProxyController::class, 'proxy']);
}

// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/auth/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/auth/clerk-sync', [AuthController::class, 'clerkSync']);
Route::post('/auth/google', [AuthController::class, 'googleLogin']);
Route::post('/auth/facebook', [AuthController::class, 'facebookLogin']);
Route::post('/auth/apple', [AuthController::class, 'appleLogin']);
Route::get('/verify-email', [AuthController::class, 'verifyEmail']);

// Public: dropdown options for KYC/profile fields (from database, not templates)
Route::get('/dropdown-options', [\App\Http\Controllers\Api\DropdownOptionController::class, 'index']);

// Public endpoints
Route::get('/packages/public', [PackageController::class, 'index']);
Route::get('/products/public', [ProductController::class, 'index']);
Route::get('/legal/terms', [LegalController::class, 'getTerms']);
Route::get('/legal/privacy', [LegalController::class, 'getPrivacy']);
Route::get('/legal/wedding-decoration-policy', [LegalController::class, 'getWeddingDecorationPolicy']);
Route::get('/legal/about', [LegalController::class, 'getAbout']);
Route::get('/legal/help', [LegalController::class, 'getHelp']);

// Fonnte WhatsApp Webhooks (No auth required — verified by token in payload)
Route::post('/webhooks/fonnte', [FonnteWebhookController::class, 'handleIncomingMessage']);
Route::post('/webhooks/fonnte/connect', [FonnteWebhookController::class, 'handleConnectionStatus']);
Route::post('/webhooks/fonnte/status', [FonnteWebhookController::class, 'handleMessageStatus']);
// Midtrans Payment Notification (No auth — verified by signature inside handler)
Route::post('/midtrans/notification', [PaymentWebhookController::class, 'notification']);
// BRI Virtual Account (BRIVA) Payment Notification (No auth — verified via X-SIGNATURE)
Route::post('/webhooks/bri/va', [BriVaWebhookController::class, 'notification']);
// BRI QRIS MPM Dinamis Payment Notification (No auth — verified via X-SIGNATURE)
Route::post('/webhooks/bri/qris', [BriVaWebhookController::class, 'qrisNotification']);
// CBIR - AI Visual Search Public Probing

Route::get('/cbir/stats', [CBIRController::class, 'getStats']);
Route::get('/cbir/health', [CBIRController::class, 'healthCheck']);
Route::get('/cbir/evaluate', [CBIRController::class, 'evaluate']);
Route::get('/cbir/arithmetic/ops', [CBIRController::class, 'arithmeticOps']);

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
    Route::post('/email/verification/send', [AuthController::class, 'sendVerificationEmail']);
    Route::get('/user', function (Request $request) {
        $user = $request->user();
        return response()->json([
            'status' => 'success',
            'data' => array_merge($user->toArray(), [
                'needs_completion' => ! $user->identity_type || ! $user->whatsapp || ! $user->birth_date,
            ]),
        ]);
    });

    Route::post('/profile', [AuthController::class, 'updateProfile']);

    // Profile
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar']);
    Route::post('/profile/change-password', [ProfileController::class, 'changePassword']);
    Route::get('/profile/dashboard', [ProfileController::class, 'dashboard']);
    Route::put('/profile/ktp', [ProfileController::class, 'updateKtp']);
    Route::post('/profile/ktp-photo', [ProfileController::class, 'uploadKtp']);
    Route::post('/profile/selfie', [ProfileController::class, 'uploadSelfie']);
    Route::post('/profile/face-scan', [ProfileController::class, 'uploadFaceScan']);
    Route::get('/profile/completion', [ProfileController::class, 'completion']);

    // App Lock
    Route::get('/profile/app-lock', [AppLockController::class, 'show']);
    Route::put('/profile/app-lock', [AppLockController::class, 'update']);
    Route::post('/profile/app-lock/pin', [AppLockController::class, 'setPin']);
    Route::post('/profile/app-lock/pin/verify', [AppLockController::class, 'verifyPin']);
    Route::post('/profile/app-lock/face-enroll', [AppLockController::class, 'faceEnroll']);
    Route::post('/profile/app-lock/face-verify', [AppLockController::class, 'faceVerify']);

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
    Route::post('/vouchers/validate', [VoucherController::class, 'validateVoucher']);
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
    Route::post('/notifications/fcm-token', [NotificationController::class, 'registerFcmToken']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);

    // User Language
    Route::get('/user/language', [UserLanguageController::class, 'show']);
    Route::put('/user/language', [UserLanguageController::class, 'update']);

    // Search
    Route::get('/vendors', [\App\Http\Controllers\Api\VendorController::class, 'index']);
    Route::get('/vendors/{id}', [\App\Http\Controllers\Api\VendorController::class, 'show']);

    Route::get('/search', [SearchController::class, 'byText']);
    Route::post('/search/image', [SearchController::class, 'byImage']);

    // Chat
    Route::get('/messages/conversations', [ChatController::class, 'getConversations']);
    Route::get('/messages/conversations/{inboxId}', [ChatController::class, 'getMessages']);
    Route::get('/messages/unread-count', [ChatController::class, 'getUnreadCount']);
    Route::get('/messages/customers', [ChatController::class, 'getCustomersForChat']);
    Route::post('/messages/send', [ChatController::class, 'sendMessage']);
    Route::post('/messages/start', [ChatController::class, 'startConversation']);
    Route::delete('/messages/{id}/delete', [ChatController::class, 'deleteMessage']);
    Route::post('/messages/{id}/star', [ChatController::class, 'starMessage']);
    Route::post('/messages/{id}/forward', [ChatController::class, 'forwardMessage']);
    Route::post('/messages/{id}/react', [ChatController::class, 'addReaction']);
    Route::post('/messages/{inboxId}/read', [ChatController::class, 'markInboxAsRead']);
    Route::post('/messages/{inboxId}/rate', [ChatController::class, 'rateInbox']);

    // Bookings / Orders
    Route::get('/bookings', [OrderController::class, 'getOrders']);
    Route::post('/bookings', [OrderController::class, 'createOrder']);
    Route::get('/bookings/{id}/payment-info', [OrderController::class, 'getPaymentInfo']);
    Route::post('/bookings/{id}/confirm-payment', [OrderController::class, 'confirmPayment']);
    Route::post('/bookings/{id}/virtual-account', [OrderController::class, 'createVirtualAccount']);
    Route::post('/bookings/{id}/qris', [OrderController::class, 'createQris']);
    Route::post('/bookings/{id}/pay', [OrderController::class, 'initiatePayment']);
    Route::post('/bookings/{id}/upload-proof', [OrderController::class, 'uploadProof']);
    Route::get('/bookings/track/{orderNumber}', [OrderController::class, 'trackOrder']);
    Route::get('/bookings/{id}', [OrderController::class, 'show']);
    Route::post('/bookings/{id}/cancel', [OrderController::class, 'cancelOrder']);
    Route::get('/bookings/{id}/invoice', [OrderController::class, 'downloadInvoice']);
    Route::post('/bookings/{id}/invoice/email', [OrderController::class, 'sendInvoiceEmail']);

    Route::get('/orders', [OrderController::class, 'getOrders']);
    Route::post('/orders', [OrderController::class, 'createOrder']);
    Route::get('/orders/{id}/payment-info', [OrderController::class, 'getPaymentInfo']);
    Route::post('/orders/{id}/confirm-payment', [OrderController::class, 'confirmPayment']);
    Route::post('/orders/{id}/virtual-account', [OrderController::class, 'createVirtualAccount']);
    Route::post('/orders/{id}/qris', [OrderController::class, 'createQris']);
    Route::post('/orders/{id}/upload-proof', [OrderController::class, 'uploadProof']);
    Route::post('/orders/{id}/pay', [OrderController::class, 'initiatePayment']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancelOrder']);
    Route::get('/orders/{id}/invoice', [OrderController::class, 'downloadInvoice']);
    Route::post('/orders/{id}/invoice/email', [OrderController::class, 'sendInvoiceEmail']);

    // Reviews
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
    Route::get('/reviews/user/{userId}', [ReviewController::class, 'getUserPublicReviews']);
    Route::get('/reviews/user', [ReviewController::class, 'getUserReviews']);
    Route::get('/reviews/package/{packageId}', 
[ReviewController::class, 'getPackageReviews']);
    Route::get('/reviews/product/{productId}', 
[ReviewController::class, 'getProductReviews']);
    Route::get('/reviews/organizer/{id}', [ReviewController::class, 'getOrganizerReviews']);

    // Wallet
    Route::get('/wallet', [WalletController::class, 'getWalletData']);
    Route::get('/wallet/history', [WalletController::class, 'getHistory']);

    // CBIR - AI Visual Search
    Route::post('/cbir/search', [CBIRController::class, 'searchSimilar']);
    Route::post('/cbir/arithmetic', [CBIRController::class, 'arithmeticSearch']);
    Route::get('/cbir/arithmetic/ops', [CBIRController::class, 'arithmeticOps']);
    Route::post('/cbir/index/product', [CBIRController::class, 'indexItem']);
    Route::post('/cbir/index/build', [CBIRController::class, 'buildIndex']);
    Route::get('/cbir/stats', [CBIRController::class, 'getStats']);
    Route::get('/cbir/evaluate', [CBIRController::class, 'evaluate']);
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

    // ─────────────────────────────────────────────────────────────────────────
    // Admin only routes (super_admin role required)
    // ─────────────────────────────────────────────────────────────────────────
    Route::prefix('admin')->middleware(\App\Http\Middleware\SuperAdmin::class)->group(function (): void {
        // Dashboard
        Route::get('/dashboard', [\App\Http\Controllers\Api\Admin\DashboardController::class, 'index']);

        // Search
        Route::get('/search', [\App\Http\Controllers\Api\SearchController::class, 'byTextAdmin']);

        // Users
        Route::get('/users', [\App\Http\Controllers\Api\Admin\UserController::class, 'index']);
        Route::get('/vendors', [\App\Http\Controllers\Api\Admin\UserController::class, 'vendors']);
        Route::get('/users/roles', [\App\Http\Controllers\Api\Admin\UserController::class, 'roles']);
        Route::get('/users/{id}', [\App\Http\Controllers\Api\Admin\UserController::class, 'show']);
        Route::post('/users', [\App\Http\Controllers\Api\Admin\UserController::class, 'store']);
        Route::put('/users/{id}', [\App\Http\Controllers\Api\Admin\UserController::class, 'update']);
        Route::delete('/users/{id}', [\App\Http\Controllers\Api\Admin\UserController::class, 'destroy']);
        Route::post('/users/{id}/toggle-active', [\App\Http\Controllers\Api\Admin\UserController::class, 'toggleActive']);
        Route::post('/users/{id}/app-lock/reset', [\App\Http\Controllers\Api\AppLockController::class, 'adminReset']);

        // Packages
        Route::get('/packages', [\App\Http\Controllers\Api\Admin\PackageController::class, 'index']);
        Route::get('/packages/{id}', [\App\Http\Controllers\Api\Admin\PackageController::class, 'show']);
        Route::post('/packages', [\App\Http\Controllers\Api\Admin\PackageController::class, 'store']);
        Route::put('/packages/{id}', [\App\Http\Controllers\Api\Admin\PackageController::class, 'update']);
        Route::delete('/packages/{id}', [\App\Http\Controllers\Api\Admin\PackageController::class, 'destroy']);
        Route::post('/packages/{id}/upload-image', [\App\Http\Controllers\Api\Admin\PackageController::class, 'uploadImage']);

        // Products
        Route::get('/products', [\App\Http\Controllers\Api\Admin\ProductController::class, 'index']);
        Route::get('/products/{id}', [\App\Http\Controllers\Api\Admin\ProductController::class, 'show']);
        Route::post('/products', [\App\Http\Controllers\Api\Admin\ProductController::class, 'store']);
        Route::put('/products/{id}', [\App\Http\Controllers\Api\Admin\ProductController::class, 'update']);
        Route::delete('/products/{id}', [\App\Http\Controllers\Api\Admin\ProductController::class, 'destroy']);
        Route::post('/products/{id}/upload-image', [\App\Http\Controllers\Api\Admin\ProductController::class, 'uploadImage']);

        // Categories
        Route::get('/categories', [\App\Http\Controllers\Api\Admin\CategoryController::class, 'index']);
        Route::get('/categories/{id}', [\App\Http\Controllers\Api\Admin\CategoryController::class, 'show']);
        Route::post('/categories', [\App\Http\Controllers\Api\Admin\CategoryController::class, 'store']);
        Route::put('/categories/{id}', [\App\Http\Controllers\Api\Admin\CategoryController::class, 'update']);
        Route::delete('/categories/{id}', [\App\Http\Controllers\Api\Admin\CategoryController::class, 'destroy']);

        // Orders
        Route::get('/orders', [\App\Http\Controllers\Api\Admin\OrderController::class, 'index']);
        Route::get('/orders/{id}', [\App\Http\Controllers\Api\Admin\OrderController::class, 'show']);
        Route::put('/orders/{id}/status', [\App\Http\Controllers\Api\Admin\OrderController::class, 'updateStatus']);
        Route::get('/orders/statuses/list', [\App\Http\Controllers\Api\Admin\OrderController::class, 'statuses']);

        // Discounts
        Route::get('/discounts', [\App\Http\Controllers\Api\Admin\DiscountController::class, 'index']);
        Route::get('/discounts/{id}', [\App\Http\Controllers\Api\Admin\DiscountController::class, 'show']);
        Route::post('/discounts', [\App\Http\Controllers\Api\Admin\DiscountController::class, 'store']);
        Route::put('/discounts/{id}', [\App\Http\Controllers\Api\Admin\DiscountController::class, 'update']);
        Route::delete('/discounts/{id}', [\App\Http\Controllers\Api\Admin\DiscountController::class, 'destroy']);

        // Vouchers
        Route::get('/vouchers', [\App\Http\Controllers\Api\Admin\VoucherController::class, 'index']);
        Route::get('/vouchers/{id}', [\App\Http\Controllers\Api\Admin\VoucherController::class, 'show']);
        Route::post('/vouchers', [\App\Http\Controllers\Api\Admin\VoucherController::class, 'store']);
        Route::put('/vouchers/{id}', [\App\Http\Controllers\Api\Admin\VoucherController::class, 'update']);
        Route::delete('/vouchers/{id}', [\App\Http\Controllers\Api\Admin\VoucherController::class, 'destroy']);

        // Reviews
        Route::get('/reviews', [\App\Http\Controllers\Api\Admin\ReviewController::class, 'index']);
        Route::get('/reviews/{id}', [\App\Http\Controllers\Api\Admin\ReviewController::class, 'show']);
        Route::put('/reviews/{id}', [\App\Http\Controllers\Api\Admin\ReviewController::class, 'update']);
        Route::delete('/reviews/{id}', [\App\Http\Controllers\Api\Admin\ReviewController::class, 'destroy']);

        // Transactions
        Route::get('/transactions', [\App\Http\Controllers\Api\Admin\TransactionController::class, 'index']);
        Route::get('/transactions/{id}', [\App\Http\Controllers\Api\Admin\TransactionController::class, 'show']);

        // Banks
        Route::get('/banks', [\App\Http\Controllers\Api\Admin\BankController::class, 'index']);
        Route::get('/banks/{id}', [\App\Http\Controllers\Api\Admin\BankController::class, 'show']);
        Route::post('/banks', [\App\Http\Controllers\Api\Admin\BankController::class, 'store']);
        Route::put('/banks/{id}', [\App\Http\Controllers\Api\Admin\BankController::class, 'update']);
        Route::delete('/banks/{id}', [\App\Http\Controllers\Api\Admin\BankController::class, 'destroy']);

        // Payment Methods
        Route::get('/payment-methods', [\App\Http\Controllers\Api\Admin\PaymentMethodController::class, 'index']);
        Route::get('/payment-methods/{id}', [\App\Http\Controllers\Api\Admin\PaymentMethodController::class, 'show']);
        Route::post('/payment-methods', [\App\Http\Controllers\Api\Admin\PaymentMethodController::class, 'store']);
        Route::put('/payment-methods/{id}', [\App\Http\Controllers\Api\Admin\PaymentMethodController::class, 'update']);
        Route::delete('/payment-methods/{id}', [\App\Http\Controllers\Api\Admin\PaymentMethodController::class, 'destroy']);

        // Help Pages
        Route::get('/helps', [\App\Http\Controllers\Api\Admin\HelpController::class, 'index']);
        Route::get('/helps/{id}', [\App\Http\Controllers\Api\Admin\HelpController::class, 'show']);
        Route::post('/helps', [\App\Http\Controllers\Api\Admin\HelpController::class, 'store']);
        Route::put('/helps/{id}', [\App\Http\Controllers\Api\Admin\HelpController::class, 'update']);
        Route::delete('/helps/{id}', [\App\Http\Controllers\Api\Admin\HelpController::class, 'destroy']);

        // Legal Pages (generic)
        Route::get('/legal-pages', [\App\Http\Controllers\Api\Admin\LegalPageController::class, 'indexPages']);
        Route::get('/legal-pages/{id}', [\App\Http\Controllers\Api\Admin\LegalPageController::class, 'showPage']);
        Route::post('/legal-pages', [\App\Http\Controllers\Api\Admin\LegalPageController::class, 'storePage']);
        Route::put('/legal-pages/{id}', [\App\Http\Controllers\Api\Admin\LegalPageController::class, 'updatePage']);
        Route::delete('/legal-pages/{id}', [\App\Http\Controllers\Api\Admin\LegalPageController::class, 'destroyPage']);

        // Terms of Service
        Route::get('/terms', [\App\Http\Controllers\Api\Admin\LegalPageController::class, 'indexTerms']);
        Route::get('/terms/{id}', [\App\Http\Controllers\Api\Admin\LegalPageController::class, 'showTerm']);
        Route::post('/terms', [\App\Http\Controllers\Api\Admin\LegalPageController::class, 'storeTerm']);
        Route::put('/terms/{id}', [\App\Http\Controllers\Api\Admin\LegalPageController::class, 'updateTerm']);
        Route::delete('/terms/{id}', [\App\Http\Controllers\Api\Admin\LegalPageController::class, 'destroyTerm']);

        // Privacy Policies
        Route::get('/privacy-policies', [\App\Http\Controllers\Api\Admin\LegalPageController::class, 'indexPolicies']);
        Route::get('/privacy-policies/{id}', [\App\Http\Controllers\Api\Admin\LegalPageController::class, 'showPolicy']);
        Route::post('/privacy-policies', [\App\Http\Controllers\Api\Admin\LegalPageController::class, 'storePolicy']);
        Route::put('/privacy-policies/{id}', [\App\Http\Controllers\Api\Admin\LegalPageController::class, 'updatePolicy']);
        Route::delete('/privacy-policies/{id}', [\App\Http\Controllers\Api\Admin\LegalPageController::class, 'destroyPolicy']);

        // Wedding Decoration Policies
        Route::get('/wedding-policies', [\App\Http\Controllers\Api\Admin\LegalPageController::class, 'indexWeddingPolicies']);
        Route::get('/wedding-policies/{id}', [\App\Http\Controllers\Api\Admin\LegalPageController::class, 'showWeddingPolicy']);
        Route::post('/wedding-policies', [\App\Http\Controllers\Api\Admin\LegalPageController::class, 'storeWeddingPolicy']);
        Route::put('/wedding-policies/{id}', [\App\Http\Controllers\Api\Admin\LegalPageController::class, 'updateWeddingPolicy']);
        Route::delete('/wedding-policies/{id}', [\App\Http\Controllers\Api\Admin\LegalPageController::class, 'destroyWeddingPolicy']);

        // Notifications
        Route::get('/notifications', [\App\Http\Controllers\Api\Admin\NotificationController::class, 'index']);
        Route::get('/notifications/{id}', [\App\Http\Controllers\Api\Admin\NotificationController::class, 'show']);
        Route::post('/notifications/send', [\App\Http\Controllers\Api\Admin\NotificationController::class, 'sendToUser']);
        Route::post('/notifications/send-bulk', [\App\Http\Controllers\Api\Admin\NotificationController::class, 'sendBulk']);
        Route::delete('/notifications/{id}', [\App\Http\Controllers\Api\Admin\NotificationController::class, 'destroy']);

        // Messages / Chat
        Route::get('/messages/inboxes', [\App\Http\Controllers\Api\Admin\MessageController::class, 'inboxes']);
        Route::get('/messages/inboxes/{id}', [\App\Http\Controllers\Api\Admin\MessageController::class, 'showInbox']);
        Route::post('/messages/send', [\App\Http\Controllers\Api\Admin\MessageController::class, 'sendMessage']);
        Route::delete('/messages/inboxes/{id}', [\App\Http\Controllers\Api\Admin\MessageController::class, 'destroyInbox']);
        Route::delete('/messages/{id}', [\App\Http\Controllers\Api\Admin\MessageController::class, 'destroyMessage']);

        // Wishlists
        Route::get('/wishlists', [\App\Http\Controllers\Api\Admin\WishlistController::class, 'index']);
        Route::delete('/wishlists/{id}', [\App\Http\Controllers\Api\Admin\WishlistController::class, 'destroy']);
    });
});
