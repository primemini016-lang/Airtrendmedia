<?php

use App\Http\Controllers\Web\InstallController;
use App\Http\Controllers\Web\PageController;
use App\Http\Controllers\Web\Auth\WebAuthController;
use App\Http\Controllers\Web\MarketplaceController;
use App\Http\Controllers\Web\GigController;
use App\Http\Controllers\Web\User\DashboardController as UserDashboard;
use App\Http\Controllers\Web\Admin\AdminController;
use App\Http\Controllers\Web\BlogController;
use App\Http\Controllers\Web\User\BlogController as UserBlogController;
use App\Http\Controllers\Web\ChatController;
use App\Http\Controllers\Web\KycController;
use App\Http\Controllers\Web\VerificationController;
use App\Http\Controllers\Web\SponsoredAdController;
use App\Http\Controllers\Web\PtcController;
use App\Http\Controllers\Web\ReviewController;
use App\Http\Controllers\Web\CommentController;
use App\Http\Controllers\Web\AccountTypeController;
use App\Http\Controllers\Web\PushNotificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Installer (bypasses the "installed" middleware).
Route::group(['prefix' => 'install', 'middleware' => ['installer.key']], function () {
    Route::get('/', [InstallController::class, 'index'])->name('install.start');
    Route::post('/', [InstallController::class, 'process'])->name('install.process');
    Route::get('/finish', [InstallController::class, 'finish'])->name('install.finish');
});

// Public pages (guarded by the "installed" middleware)
Route::middleware('installed')->group(function () {

    Route::get('/', [PageController::class, 'home'])->name('home');
    Route::get('/browse', [PageController::class, 'browse'])->name('browse');
    Route::get('/faqs', [PageController::class, 'faqs'])->name('faqs');
    Route::get('/contact', [PageController::class, 'contact'])->name('contact');
    Route::post('/contact', [PageController::class, 'sendContact']);
    Route::get('/affiliate', [PageController::class, 'affiliate'])->name('affiliate.info');
    Route::get('/terms', [PageController::class, 'terms'])->name('terms');

    // Marketplace (public browse)
    Route::get('/marketplace', [MarketplaceController::class, 'index'])->name('marketplace');
    Route::get('/marketplace/{listing}', [MarketplaceController::class, 'show'])->name('marketplace.show')->where('listing', '[0-9]+');

    // Gigs (public browse)
    Route::get('/gigs', [GigController::class, 'index'])->name('gigs.browse');
    Route::get('/gigs/{gig}', [GigController::class, 'show'])->name('gigs.show')->where('gig', '[0-9]+');

    // Blog (public browse — Phoenix-style full screen)
    Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
    Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

    // PTC (Paid-To-Click) ads — public browse
    Route::get('/ptc', [PtcController::class, 'index'])->name('ptc.index');
    Route::get('/ptc/{ad}', [PtcController::class, 'show'])->name('ptc.show')
        ->where('ad', '[0-9]+');

    // Auth (web session-based for the blade frontend)
    Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [WebAuthController::class, 'login']);
    Route::get('/register', [WebAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [WebAuthController::class, 'register']);
    Route::get('/verify', [WebAuthController::class, 'showVerify'])->name('verify');
    Route::post('/verify', [WebAuthController::class, 'verify']);
    Route::get('/reset', [WebAuthController::class, 'showReset'])->name('password.request');
    Route::post('/reset', [WebAuthController::class, 'sendReset']);
    Route::get('/reset/confirm', [WebAuthController::class, 'showResetConfirm'])->name('password.reset');
    Route::post('/reset/confirm', [WebAuthController::class, 'resetConfirm']);

    // Authenticated user area (session-based via the 'web' guard)
    Route::middleware('auth:web')->group(function () {
        Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

        // Stop impersonation (admin returns to admin panel)
        Route::get('/stop-impersonating', [AdminController::class, 'stopImpersonating'])->name('stop-impersonating');

        // Notifications
        Route::get('/notifications', [UserDashboard::class, 'notifications'])->name('user.notifications');
        Route::post('/notifications/{notification}/read', [UserDashboard::class, 'markNotificationRead'])->name('user.notifications.read');
        Route::post('/notifications/read-all', [UserDashboard::class, 'markAllNotificationsRead'])->name('user.notifications.read-all');
        Route::get('/notifications/unread-count', [UserDashboard::class, 'unreadNotificationCount'])->name('user.notifications.unread-count');

        Route::get('/activate', [UserDashboard::class, 'activate'])->name('user.activate');
        Route::post('/activate/initiate', [UserDashboard::class, 'initiateActivation']);
        Route::get('/activate/verify', [UserDashboard::class, 'verifyActivation'])->name('user.activate.verify');
        Route::post('/activate/trial', [UserDashboard::class, 'startTrial'])->name('user.activate.trial');

        Route::middleware('activated')->group(function () {
            Route::get('/dashboard', [UserDashboard::class, 'index'])->name('user.dashboard');
            Route::get('/tasks', [UserDashboard::class, 'browseTasks'])->name('user.tasks');
            Route::get('/tasks/{task}', [UserDashboard::class, 'taskDetail'])->name('user.task')->where('task', '[0-9]+');
            Route::post('/tasks/{task}/book', [UserDashboard::class, 'bookTask'])->name('user.task.book');
            Route::post('/tasks/{task}/proof', [UserDashboard::class, 'submitProof'])->name('user.task.proof');
            Route::get('/my-bookings', [UserDashboard::class, 'myBookings'])->name('user.bookings');
            Route::get('/my-tasks', [UserDashboard::class, 'myTasks'])->name('user.offers');
            Route::get('/my-tasks/{task}', [UserDashboard::class, 'taskProofs'])->name('user.task.proofs');
            Route::post('/proofs/{proof}/approve', [UserDashboard::class, 'approveProof'])->name('user.proof.approve');
            Route::post('/proofs/{proof}/reject', [UserDashboard::class, 'rejectProof'])->name('user.proof.reject');
            Route::get('/create-task', [UserDashboard::class, 'createTask'])->name('user.task.create');
            Route::post('/create-task', [UserDashboard::class, 'storeTask'])->name('user.task.store');

            // ===== Gig creation system (user-facing) =====
            Route::get('/my-gigs', [GigController::class, 'myGigs'])->name('user.gigs.index');
            Route::get('/my-gigs/create', [GigController::class, 'create'])->name('user.gigs.create');
            Route::post('/my-gigs', [GigController::class, 'store'])->name('user.gigs.store');
            Route::get('/my-gigs/{gig}/edit', [GigController::class, 'edit'])->name('user.gigs.edit');
            Route::post('/my-gigs/{gig}', [GigController::class, 'update'])->name('user.gigs.update');
            Route::delete('/my-gigs/{gig}', [GigController::class, 'destroy'])->name('user.gigs.destroy');

            // ===== Marketplace listing creation system (user-facing) =====
            Route::get('/my-listings', [MarketplaceController::class, 'myListings'])->name('user.marketplace.index');
            Route::get('/my-listings/create', [MarketplaceController::class, 'create'])->name('user.marketplace.create');
            Route::post('/my-listings', [MarketplaceController::class, 'store'])->name('user.marketplace.store');
            Route::get('/my-listings/{listing}/edit', [MarketplaceController::class, 'edit'])->name('user.marketplace.edit');
            Route::post('/my-listings/{listing}', [MarketplaceController::class, 'update'])->name('user.marketplace.update');
            Route::delete('/my-listings/{listing}', [MarketplaceController::class, 'destroy'])->name('user.marketplace.destroy');
            Route::get('/wallet', [UserDashboard::class, 'wallet'])->name('user.wallet');
            Route::post('/wallet/deposit', [UserDashboard::class, 'initiateDeposit'])->name('user.wallet.deposit');
            Route::get('/wallet/deposit/verify', [UserDashboard::class, 'verifyDeposit'])->name('user.wallet.verify');
            Route::get('/withdraw', [UserDashboard::class, 'withdraw'])->name('user.withdraw');
            Route::post('/withdraw', [UserDashboard::class, 'requestWithdraw'])->name('user.withdraw.request');
            Route::get('/transactions', [UserDashboard::class, 'transactions'])->name('user.transactions');
            Route::get('/affiliate-dashboard', [UserDashboard::class, 'affiliate'])->name('user.affiliate');
            Route::get('/profile', [UserDashboard::class, 'profile'])->name('user.profile');
            Route::post('/profile', [UserDashboard::class, 'updateProfile'])->name('user.profile.update');
            Route::post('/profile/image', [UserDashboard::class, 'updateImage'])->name('user.profile.image');
            Route::get('/messages', [UserDashboard::class, 'messages'])->name('user.messages');
            Route::post('/messages', [UserDashboard::class, 'sendMessage'])->name('user.messages.send');

            // ===== Blog interactions (auth required) =====
            Route::post('/blog/{slug}/like', [BlogController::class, 'toggleLike'])->name('blog.like');
            Route::post('/blog/{slug}/rate', [BlogController::class, 'rate'])->name('blog.rate');
            Route::post('/blog/{slug}/comment', [BlogController::class, 'comment'])->name('blog.comment');
            Route::post('/blog/{slug}/share', [BlogController::class, 'share'])->name('blog.share');

            // ===== Airtrendmedia — Messenger / User-to-User Chat =====
            Route::get('/messenger', [ChatController::class, 'index'])->name('messenger.chat');
            Route::get('/messenger/start/{targetUser}', [ChatController::class, 'startConversation'])->name('messenger.chat.start');
            Route::post('/messenger/{conversation}/send', [ChatController::class, 'send'])->name('messenger.chat.send');
            Route::get('/messenger/{conversation}/fetch', [ChatController::class, 'fetchMessages'])->name('messenger.chat.fetch');
            Route::get('/messenger/unread', [ChatController::class, 'unreadCount'])->name('messenger.chat.unread');
            Route::post('/messenger/group', [ChatController::class, 'createGroup'])->name('messenger.chat.group');
            Route::delete('/messenger/message/{message}', [ChatController::class, 'deleteMessage'])->name('messenger.chat.delete');
            Route::get('/messenger/search', [ChatController::class, 'searchUsers'])->name('messenger.chat.search');
            Route::post('/messenger/{conversation}/typing', [ChatController::class, 'typing'])->name('messenger.chat.typing');
            Route::get('/messenger/{conversation}/typing-status', [ChatController::class, 'typingStatus'])->name('messenger.chat.typing-status');
            Route::post('/messenger/message/{message}/react', [ChatController::class, 'react'])->name('messenger.chat.react');
            Route::get('/messenger/message/{message}/reactions', [ChatController::class, 'messageReactions'])->name('messenger.chat.reactions');

            // ===== Airtrendmedia — KYC Verification =====
            Route::get('/kyc', [KycController::class, 'index'])->name('user.kyc');
            Route::post('/kyc/submit', [KycController::class, 'submit'])->name('user.kyc.submit');

            // ===== Airtrendmedia — Blue Verification Badge =====
            Route::get('/verification', [VerificationController::class, 'index'])->name('user.verification');
            Route::post('/verification/apply', [VerificationController::class, 'apply'])->name('user.verification.apply');
            Route::post('/verification/renew', [VerificationController::class, 'renew'])->name('user.verification.renew');

            // ===== Airtrendmedia — Account Type Switch (Freelancer / Advertiser / Both) =====
            Route::get('/account-type', [AccountTypeController::class, 'index'])->name('user.account-type');
            Route::post('/account-type/switch', [AccountTypeController::class, 'switch'])->name('user.account-type.switch');

            // ===== Airtrendmedia — Sponsored Ads (Advertiser) =====
            Route::get('/sponsored-ads', [SponsoredAdController::class, 'index'])->name('user.sponsored-ads');
            Route::post('/sponsored-ads', [SponsoredAdController::class, 'store'])->name('user.sponsored-ads.store');
            Route::get('/sponsored-ads/{ad}/stats', [SponsoredAdController::class, 'stats'])->name('user.sponsored-ads.stats');
            Route::post('/sponsored-ads/{ad}/impression', [SponsoredAdController::class, 'impression'])->name('user.sponsored-ads.impression');
            Route::post('/sponsored-ads/{ad}/click', [SponsoredAdController::class, 'click'])->name('user.sponsored-ads.click');
            Route::get('/feed-ads', [SponsoredAdController::class, 'feedAds'])->name('feed.ads');

            // ===== Airtrendmedia — PTC (Paid-To-Click) — User side =====
            Route::get('/my-ptc', [PtcController::class, 'myAds'])->name('user.ptc.index');
            Route::get('/my-ptc/create', [PtcController::class, 'create'])->name('user.ptc.create');
            Route::post('/my-ptc', [PtcController::class, 'store'])->name('user.ptc.store');
            Route::get('/my-ptc/history', [PtcController::class, 'history'])->name('user.ptc.history');
            Route::get('/my-ptc/{ad}/edit', [PtcController::class, 'edit'])->name('user.ptc.edit');
            Route::post('/my-ptc/{ad}', [PtcController::class, 'update'])->name('user.ptc.update');
            Route::delete('/my-ptc/{ad}', [PtcController::class, 'destroy'])->name('user.ptc.destroy');
            Route::get('/my-ptc/{ad}/stats', [PtcController::class, 'stats'])->name('user.ptc.stats');
            Route::post('/ptc/{ad}/start', [PtcController::class, 'startView'])->name('ptc.start');
            Route::post('/ptc/{ad}/confirm', [PtcController::class, 'confirmExecution'])->name('ptc.confirm');

            // ===== Creator Blog Studio =====
            Route::get('/my-blog', [UserBlogController::class, 'index'])->name('user.blog.index');
            Route::get('/my-blog/create', [UserBlogController::class, 'create'])->name('user.blog.create');
            Route::post('/my-blog', [UserBlogController::class, 'store'])->name('user.blog.store');
            Route::get('/my-blog/{post}/edit', [UserBlogController::class, 'edit'])->name('user.blog.edit');
            Route::post('/my-blog/{post}', [UserBlogController::class, 'update'])->name('user.blog.update');
            Route::delete('/my-blog/{post}', [UserBlogController::class, 'destroy'])->name('user.blog.destroy');

            // ===== Airtrendmedia — Unified Reviews / Ratings (gigs, listings, tasks, profiles) =====
            Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
            Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

            // ===== Airtrendmedia — Comments (marketplace listings, reusable) =====
            Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');
            Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

            // ===== Airtrendmedia — Push Notification Device Token Registration =====
            Route::post('/push/register-token', [PushNotificationController::class, 'registerToken'])->name('user.push.register-token');
            Route::post('/push/unregister-token', [PushNotificationController::class, 'unregisterToken'])->name('user.push.unregister-token');
        });
    });

    // Admin panel
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/login', [AdminController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminController::class, 'login']);
        Route::post('/logout', [AdminController::class, 'logout'])->name('logout');

        Route::middleware('auth:admin')->group(function () {
            Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
            Route::get('/users', [AdminController::class, 'users'])->name('users');
            Route::get('/users/{user}', [AdminController::class, 'userShow'])->name('users.show');
            Route::post('/users/{user}/ban', [AdminController::class, 'userBan'])->name('users.ban');
            Route::post('/users/{user}/activate', [AdminController::class, 'userActivate'])->name('users.activate');
            Route::post('/users/{user}/balance', [AdminController::class, 'userBalance'])->name('users.balance');
            Route::get('/deposits', [AdminController::class, 'deposits'])->name('deposits');
            Route::post('/deposits/{deposit}/approve', [AdminController::class, 'depositApprove'])->name('deposits.approve');
            Route::post('/deposits/{deposit}/reject', [AdminController::class, 'depositReject'])->name('deposits.reject');
            Route::get('/withdrawals', [AdminController::class, 'withdrawals'])->name('withdrawals');
            Route::post('/withdrawals/{withdrawal}/paid', [AdminController::class, 'withdrawalPaid'])->name('withdrawals.paid');
            Route::post('/withdrawals/{withdrawal}/reject', [AdminController::class, 'withdrawalReject'])->name('withdrawals.reject');
            Route::get('/tasks', [AdminController::class, 'tasks'])->name('tasks');
            Route::post('/tasks/{task}/approve', [AdminController::class, 'taskApprove'])->name('tasks.approve');
            Route::post('/tasks/{task}/reject', [AdminController::class, 'taskReject'])->name('tasks.reject');
            Route::delete('/tasks/{task}', [AdminController::class, 'taskDelete'])->name('tasks.delete');
            Route::get('/categories', [AdminController::class, 'categories'])->name('categories');
            Route::post('/categories', [AdminController::class, 'categoryStore'])->name('categories.store');
            Route::post('/categories/{category}', [AdminController::class, 'categoryUpdate'])->name('categories.update');
            Route::delete('/categories/{category}', [AdminController::class, 'categoryDelete'])->name('categories.delete');
            Route::get('/complaints', [AdminController::class, 'complaints'])->name('complaints');
            Route::post('/complaints/{complaint}/resolve', [AdminController::class, 'complaintResolve'])->name('complaints.resolve');
            Route::get('/messages', [AdminController::class, 'messages'])->name('messages');
            Route::get('/messages/{user}', [AdminController::class, 'messageShow'])->name('messages.show');
            Route::post('/messages/{user}', [AdminController::class, 'messageReply'])->name('messages.reply');
            Route::get('/faqs', [AdminController::class, 'faqs'])->name('faqs');
            Route::post('/faqs', [AdminController::class, 'faqStore'])->name('faqs.store');
            Route::post('/faqs/{faq}', [AdminController::class, 'faqUpdate'])->name('faqs.update');
            Route::delete('/faqs/{faq}', [AdminController::class, 'faqDelete'])->name('faqs.delete');
            Route::get('/currencies', [AdminController::class, 'currencies'])->name('currencies');
            Route::post('/currencies', [AdminController::class, 'currencyStore'])->name('currencies.store');
            Route::post('/currencies/{currency}', [AdminController::class, 'currencyUpdate'])->name('currencies.update');
            Route::delete('/currencies/{currency}', [AdminController::class, 'currencyDelete'])->name('currencies.delete');
            Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
            Route::post('/settings', [AdminController::class, 'settingsUpdate'])->name('settings.update');
            Route::get('/methods', [AdminController::class, 'methods'])->name('methods');
            Route::post('/methods/deposit', [AdminController::class, 'depositMethodStore'])->name('methods.deposit.store');
            Route::post('/methods/deposit/{method}', [AdminController::class, 'depositMethodUpdate'])->name('methods.deposit.update');
            Route::delete('/methods/deposit/{method}', [AdminController::class, 'depositMethodDelete'])->name('methods.deposit.delete');
            Route::post('/methods/withdrawal', [AdminController::class, 'withdrawalMethodStore'])->name('methods.withdrawal.store');
            Route::post('/methods/withdrawal/{method}', [AdminController::class, 'withdrawalMethodUpdate'])->name('methods.withdrawal.update');
            Route::delete('/methods/withdrawal/{method}', [AdminController::class, 'withdrawalMethodDelete'])->name('methods.withdrawal.delete');
            Route::get('/transactions', [AdminController::class, 'transactions'])->name('transactions');
            Route::get('/affiliate', [AdminController::class, 'affiliate'])->name('affiliate');
            Route::post('/affiliate/{referral}/revoke', [AdminController::class, 'affiliateRevoke'])->name('affiliate.revoke');

            // Login as user (impersonation)
            Route::get('/users/{user}/login-as', [AdminController::class, 'loginAsUser'])->name('users.login-as');

            // Ads management
            Route::get('/ads', [AdminController::class, 'ads'])->name('ads');
            Route::post('/ads', [AdminController::class, 'adStore'])->name('ads.store');
            Route::post('/ads/{ad}', [AdminController::class, 'adUpdate'])->name('ads.update');
            Route::delete('/ads/{ad}', [AdminController::class, 'adDelete'])->name('ads.delete');

            // Appearance (CSS, HTML, logo, favicon)
            Route::get('/appearance', [AdminController::class, 'appearance'])->name('appearance');
            Route::post('/appearance', [AdminController::class, 'appearanceUpdate'])->name('appearance.update');
            Route::post('/appearance/logo', [AdminController::class, 'uploadLogo'])->name('appearance.logo');
            Route::post('/appearance/favicon', [AdminController::class, 'uploadFavicon'])->name('appearance.favicon');

            // Error screen content management (logo, heading, subtext, color)
            Route::post('/appearance/error-screen', [AdminController::class, 'errorScreenUpdate'])->name('appearance.error-screen');
            Route::post('/appearance/error-screen-logo', [AdminController::class, 'uploadErrorLogo'])->name('appearance.error-screen-logo');

            // Email / SMTP settings
            Route::get('/email-settings', [AdminController::class, 'emailSettings'])->name('email-settings');
            Route::post('/email-settings', [AdminController::class, 'emailSettingsUpdate'])->name('email-settings.update');
            Route::post('/email-settings/test', [AdminController::class, 'sendTestEmail'])->name('email-settings.test');

            // Payment API keys
            Route::get('/payment-keys', [AdminController::class, 'paymentKeys'])->name('payment-keys');
            Route::post('/payment-keys', [AdminController::class, 'paymentKeysUpdate'])->name('payment-keys.update');

            // System update (GitHub auto-update)
            Route::get('/system-update', [AdminController::class, 'systemUpdate'])->name('system-update');
            Route::post('/system-update', [AdminController::class, 'runSystemUpdate'])->name('system-update.run');
            Route::post('/system-update/diagnostics', [AdminController::class, 'runDiagnostics'])->name('system-update.diagnostics');

            // Gigs management
            Route::get('/gigs', [AdminController::class, 'gigs'])->name('gigs');
            Route::post('/gigs/{gig}/approve', [AdminController::class, 'gigApprove'])->name('gigs.approve');
            Route::post('/gigs/{gig}/reject', [AdminController::class, 'gigReject'])->name('gigs.reject');
            Route::delete('/gigs/{gig}', [AdminController::class, 'gigDelete'])->name('gigs.delete');

            // Marketplace management
            Route::get('/marketplace', [AdminController::class, 'marketplace'])->name('marketplace');
            Route::post('/marketplace/{listing}/approve', [AdminController::class, 'marketplaceApprove'])->name('marketplace.approve');
            Route::post('/marketplace/{listing}/reject', [AdminController::class, 'marketplaceReject'])->name('marketplace.reject');
            Route::delete('/marketplace/{listing}', [AdminController::class, 'marketplaceDelete'])->name('marketplace.delete');

            // Notifications (broadcast)
            Route::get('/notifications', [AdminController::class, 'adminNotifications'])->name('notifications');
            Route::post('/notifications/broadcast', [AdminController::class, 'sendBroadcastNotification'])->name('notifications.broadcast');

            // Blog management
            Route::get('/blog', [BlogController::class, 'adminIndex'])->name('blog');
            Route::post('/blog', [BlogController::class, 'adminStore'])->name('blog.store');
            Route::get('/blog/{post}/edit', [BlogController::class, 'adminEdit'])->name('blog.edit');
            Route::post('/blog/{post}', [BlogController::class, 'adminUpdate'])->name('blog.update');
            Route::delete('/blog/{post}', [BlogController::class, 'adminDelete'])->name('blog.delete');
            Route::post('/blog/categories', [BlogController::class, 'adminCategoryStore'])->name('blog.category.store');
            Route::delete('/blog/categories/{category}', [BlogController::class, 'adminCategoryDelete'])->name('blog.category.delete');

            // ===== Airtrendmedia — Admin KYC Management =====
            Route::get('/kyc', [KycController::class, 'adminIndex'])->name('kyc');
            Route::get('/kyc/{kyc}', [KycController::class, 'adminShow'])->name('kyc.show');
            Route::post('/kyc/{kyc}/approve', [KycController::class, 'adminApprove'])->name('kyc.approve');
            Route::post('/kyc/{kyc}/reject', [KycController::class, 'adminReject'])->name('kyc.reject');
            Route::post('/kyc/auto-approval', [KycController::class, 'adminToggleAuto'])->name('kyc.auto-approval');

            // ===== Airtrendmedia — Admin Verification Badge Management =====
            Route::get('/verification', [VerificationController::class, 'adminIndex'])->name('verification');
            Route::get('/verification/{badge}', [VerificationController::class, 'adminShow'])->name('verification.show');
            Route::post('/verification/{badge}/approve', [VerificationController::class, 'adminApprove'])->name('verification.approve');
            Route::post('/verification/{badge}/reject', [VerificationController::class, 'adminReject'])->name('verification.reject');
            Route::post('/verification/{badge}/revoke', [VerificationController::class, 'adminRevoke'])->name('verification.revoke');
            Route::post('/verification/settings', [VerificationController::class, 'adminSettings'])->name('verification.settings');

            // ===== Airtrendmedia — Admin Sponsored Ads Management =====
            Route::get('/sponsored-ads', [SponsoredAdController::class, 'adminIndex'])->name('sponsored-ads');
            Route::post('/sponsored-ads/{ad}/approve', [SponsoredAdController::class, 'adminApprove'])->name('sponsored-ads.approve');
            Route::post('/sponsored-ads/{ad}/reject', [SponsoredAdController::class, 'adminReject'])->name('sponsored-ads.reject');
            Route::post('/sponsored-ads/{ad}/extend-cpc', [SponsoredAdController::class, 'adminExtendCpc'])->name('sponsored-ads.extend-cpc');
            Route::post('/sponsored-ads/{ad}/pause', [SponsoredAdController::class, 'adminPause'])->name('sponsored-ads.pause');
            Route::post('/sponsored-ads/{ad}/resume', [SponsoredAdController::class, 'adminResume'])->name('sponsored-ads.resume');
            Route::post('/sponsored-ads/settings', [SponsoredAdController::class, 'adminSettings'])->name('sponsored-ads.settings');

            // ===== Airtrendmedia — Admin PTC (Paid-To-Click) Management =====
            Route::get('/ptc', [PtcController::class, 'adminIndex'])->name('ptc');
            Route::get('/ptc/settings', [PtcController::class, 'adminSettings'])->name('ptc.settings');
            Route::post('/ptc/settings', [PtcController::class, 'adminSettings'])->name('ptc.settings');
            Route::get('/ptc/{ad}', [PtcController::class, 'adminShow'])->name('ptc.show')
                ->where('ad', '[0-9]+');
            Route::post('/ptc/{ad}/approve', [PtcController::class, 'adminApprove'])->name('ptc.approve')
                ->where('ad', '[0-9]+');
            Route::post('/ptc/{ad}/reject', [PtcController::class, 'adminReject'])->name('ptc.reject')
                ->where('ad', '[0-9]+');
            Route::post('/ptc/{ad}/pause', [PtcController::class, 'adminPause'])->name('ptc.pause')
                ->where('ad', '[0-9]+');
            Route::post('/ptc/{ad}/resume', [PtcController::class, 'adminResume'])->name('ptc.resume')
                ->where('ad', '[0-9]+');
            Route::delete('/ptc/{ad}', [PtcController::class, 'adminDestroy'])->name('ptc.delete')
                ->where('ad', '[0-9]+');

            // ===== Airtrendmedia — Admin Push / Firebase / OneSignal Settings =====
            Route::get('/push-settings', [PushNotificationController::class, 'adminSettings'])->name('push-settings');
            Route::post('/push-settings', [PushNotificationController::class, 'adminSettingsSave'])->name('push-settings.save');
            Route::post('/push-settings/test', [PushNotificationController::class, 'adminTestPush'])->name('push-settings.test');
            Route::get('/push-settings/logs', [PushNotificationController::class, 'adminLogs'])->name('push-settings.logs');
            Route::post('/push-settings/broadcast', [PushNotificationController::class, 'adminBroadcast'])->name('push-settings.broadcast');

            // ===== Airtrendmedia — Admin Anti-Cheat Flags =====
            Route::get('/anti-cheat', [AdminController::class, 'antiCheat'])->name('anti-cheat');
            Route::post('/anti-cheat/{flag}/resolve', [AdminController::class, 'antiCheatResolve'])->name('anti-cheat.resolve');
            Route::post('/anti-cheat/{flag}/dismiss', [AdminController::class, 'antiCheatDismiss'])->name('anti-cheat.dismiss');

            // ===== Airtrendmedia — Admin PWA + Popup Banner Settings =====
            Route::get('/pwa-settings', [AdminController::class, 'pwaSettings'])->name('pwa-settings');
            Route::post('/pwa-settings', [AdminController::class, 'pwaSettingsSave'])->name('pwa-settings.save');
            Route::get('/banner-settings', [AdminController::class, 'bannerSettings'])->name('banner-settings');
            Route::post('/banner-settings', [AdminController::class, 'bannerSettingsSave'])->name('banner-settings.save');
        });
    });
});
