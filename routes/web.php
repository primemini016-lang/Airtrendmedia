<?php

use App\Http\Controllers\Web\InstallController;
use App\Http\Controllers\Web\PageController;
use App\Http\Controllers\Web\Auth\WebAuthController;
use App\Http\Controllers\Web\MarketplaceController;
use App\Http\Controllers\Web\GigController;
use App\Http\Controllers\Web\User\DashboardController as UserDashboard;
use App\Http\Controllers\Web\Admin\AdminController;
use App\Http\Controllers\Web\BlogController;
use App\Http\Controllers\Web\SocialController;
use App\Http\Controllers\Web\SocialPageController;
use App\Http\Controllers\Web\GroupController;
use App\Http\Controllers\Web\ChatController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\MonetizationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Installer (bypasses the "installed" middleware)
Route::group(['prefix' => 'install'], function () {
    Route::get('/', [InstallController::class, 'index'])->name('install.start');
    Route::get('/requirements', [InstallController::class, 'requirements'])->name('install.requirements');
    Route::get('/database', [InstallController::class, 'database'])->name('install.database');
    Route::post('/database', [InstallController::class, 'runDatabase']);
    Route::get('/app', [InstallController::class, 'appSetup'])->name('install.app');
    Route::post('/app', [InstallController::class, 'saveApp']);
    Route::get('/admin', [InstallController::class, 'adminSetup'])->name('install.admin');
    Route::post('/admin', [InstallController::class, 'saveAdmin']);
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
    Route::get('/marketplace/{listing}', [MarketplaceController::class, 'show'])->name('marketplace.show');

    // Gigs (public browse)
    Route::get('/gigs', [GigController::class, 'index'])->name('gigs.browse');
    Route::get('/gigs/{gig}', [GigController::class, 'show'])->name('gigs.show');

    // Blog (public browse — Phoenix-style full screen)
    Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
    Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

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
            Route::get('/tasks/{task}', [UserDashboard::class, 'taskDetail'])->name('user.task');
            Route::post('/tasks/{task}/book', [UserDashboard::class, 'bookTask'])->name('user.task.book');
            Route::post('/tasks/{task}/proof', [UserDashboard::class, 'submitProof'])->name('user.task.proof');
            Route::get('/my-bookings', [UserDashboard::class, 'myBookings'])->name('user.bookings');
            Route::get('/my-tasks', [UserDashboard::class, 'myTasks'])->name('user.offers');
            Route::get('/my-tasks/{task}', [UserDashboard::class, 'taskProofs'])->name('user.task.proofs');
            Route::post('/proofs/{proof}/approve', [UserDashboard::class, 'approveProof'])->name('user.proof.approve');
            Route::post('/proofs/{proof}/reject', [UserDashboard::class, 'rejectProof'])->name('user.proof.reject');
            Route::get('/create-task', [UserDashboard::class, 'createTask'])->name('user.task.create');
            Route::post('/create-task', [UserDashboard::class, 'storeTask'])->name('user.task.store');
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

            // ===== Facebook Clone — Social Feed =====
            Route::get('/social', [SocialController::class, 'feed'])->name('social.feed');
            Route::post('/social/post', [SocialController::class, 'createPost'])->name('social.feed.post');
            Route::post('/social/post/{post}/like', [SocialController::class, 'toggleLike'])->name('social.like');
            Route::post('/social/post/{post}/comment', [SocialController::class, 'comment'])->name('social.comment');
            Route::post('/social/comment/{comment}/like', [SocialController::class, 'toggleCommentLike'])->name('social.comment.like');
            Route::post('/social/post/{post}/share', [SocialController::class, 'share'])->name('social.share');
            Route::delete('/social/post/{post}', [SocialController::class, 'deletePost'])->name('social.delete');
            Route::get('/social/post/{post}/comments', [SocialController::class, 'getComments'])->name('social.comments');
            Route::post('/social/follow/{target}', [SocialController::class, 'toggleFollow'])->name('social.follow');
            Route::get('/explore', [SocialController::class, 'explore'])->name('social.explore');

            // ===== Facebook Clone — Pages =====
            Route::get('/pages', [SocialPageController::class, 'index'])->name('social.pages');
            Route::get('/pages/create', [SocialPageController::class, 'create'])->name('social.pages.create');
            Route::post('/pages', [SocialPageController::class, 'store'])->name('social.pages.store');
            Route::get('/pages/mine', [SocialPageController::class, 'myPages'])->name('social.pages.mine');
            Route::get('/pages/{page:slug}', [SocialPageController::class, 'show'])->name('social.page.show');
            Route::get('/pages/{page:slug}/edit', [SocialPageController::class, 'edit'])->name('social.page.edit');
            Route::post('/pages/{page:slug}', [SocialPageController::class, 'update'])->name('social.page.update');
            Route::delete('/pages/{page:slug}', [SocialPageController::class, 'destroy'])->name('social.page.destroy');
            Route::post('/pages/{page}/join', [SocialPageController::class, 'join'])->name('social.pages.join');
            Route::post('/pages/{page}/leave', [SocialPageController::class, 'leave'])->name('social.pages.leave');

            // ===== Facebook Clone — Groups =====
            Route::get('/groups', [GroupController::class, 'index'])->name('social.groups');
            Route::get('/groups/create', [GroupController::class, 'create'])->name('social.groups.create');
            Route::post('/groups', [GroupController::class, 'store'])->name('social.groups.store');
            Route::get('/groups/mine', [GroupController::class, 'myGroups'])->name('social.groups.mine');
            Route::get('/groups/{group:slug}', [GroupController::class, 'show'])->name('social.group.show');
            Route::get('/groups/{group:slug}/edit', [GroupController::class, 'edit'])->name('social.group.edit');
            Route::post('/groups/{group:slug}', [GroupController::class, 'update'])->name('social.group.update');
            Route::delete('/groups/{group:slug}', [GroupController::class, 'destroy'])->name('social.group.destroy');
            Route::post('/groups/{group}/join', [GroupController::class, 'join'])->name('social.groups.join');
            Route::post('/groups/{group}/leave', [GroupController::class, 'leave'])->name('social.groups.leave');
            Route::post('/groups/{group}/members/{memberId}/approve', [GroupController::class, 'approveMember'])->name('social.groups.member.approve');
            Route::post('/groups/{group}/members/{memberId}/remove', [GroupController::class, 'removeMember'])->name('social.groups.member.remove');

            // ===== Facebook Clone — Messenger / Chat =====
            Route::get('/messenger', [ChatController::class, 'index'])->name('social.chat');
            Route::get('/messenger/start/{targetUser}', [ChatController::class, 'startConversation'])->name('social.chat.start');
            Route::post('/messenger/{conversation}/send', [ChatController::class, 'send'])->name('social.chat.send');
            Route::get('/messenger/{conversation}/fetch', [ChatController::class, 'fetchMessages'])->name('social.chat.fetch');
            Route::get('/messenger/unread', [ChatController::class, 'unreadCount'])->name('social.chat.unread');
            Route::post('/messenger/group', [ChatController::class, 'createGroup'])->name('social.chat.group');
            Route::delete('/messenger/message/{message}', [ChatController::class, 'deleteMessage'])->name('social.chat.delete');
            Route::get('/messenger/search', [ChatController::class, 'searchUsers'])->name('social.chat.search');

            // ===== Facebook Clone — Profiles =====
            Route::get('/profile/{username}', [ProfileController::class, 'show'])->name('social.profile');
            Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('social.profile.edit');
            Route::post('/profile/update', [ProfileController::class, 'update'])->name('social.profile.update');
            Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('social.profile.avatar');
            Route::post('/profile/{creator}/stars', [ProfileController::class, 'sendStar'])->name('social.stars');
            Route::get('/people', [ProfileController::class, 'suggestions'])->name('social.suggestions');
            Route::get('/friends', [ProfileController::class, 'friends'])->name('social.friends');

            // ===== Facebook Clone — Monetization =====
            Route::get('/monetization', [MonetizationController::class, 'dashboard'])->name('social.monetization');
            Route::post('/monetization/apply', [MonetizationController::class, 'apply'])->name('social.monetization.apply');
            Route::post('/monetization/subscription', [MonetizationController::class, 'setupSubscription'])->name('social.monetization.subscription');
            Route::post('/monetization/withdraw', [MonetizationController::class, 'withdraw'])->name('social.monetization.withdraw');
            Route::post('/monetization/subscribe/{creator}', [MonetizationController::class, 'subscribe'])->name('social.monetization.subscribe');
            Route::post('/monetization/subscription/{subscription}/cancel', [MonetizationController::class, 'cancelSubscription'])->name('social.monetization.cancel');
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
        });
    });
});
