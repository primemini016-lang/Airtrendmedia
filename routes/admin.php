<?php

use App\Http\Controllers\Api\Admin\AdminAffiliateController;
use App\Http\Controllers\Api\Admin\AdminAppController;
use App\Http\Controllers\Api\Admin\AdminAuthController;
use App\Http\Controllers\Api\Admin\AdminCategoryController;
use App\Http\Controllers\Api\Admin\AdminComplaintController;
use App\Http\Controllers\Api\Admin\AdminCurrencyController;
use App\Http\Controllers\Api\Admin\AdminDashboardController;
use App\Http\Controllers\Api\Admin\AdminDepositController;
use App\Http\Controllers\Api\Admin\AdminFaqController;
use App\Http\Controllers\Api\Admin\AdminMessageController;
use App\Http\Controllers\Api\Admin\AdminMethodController;
use App\Http\Controllers\Api\Admin\AdminTaskController;
use App\Http\Controllers\Api\Admin\AdminTransactionController;
use App\Http\Controllers\Api\Admin\AdminUserController;
use App\Http\Controllers\Api\Admin\AdminWithdrawalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin API Routes  (mounted at /api/admin in RouteServiceProvider)
|--------------------------------------------------------------------------
*/

// Public admin auth
Route::post('/login', [AdminAuthController::class, 'login']);

// Authenticated admin (jwt.auth on 'admin' guard via the 'admin' middleware)
Route::middleware(['admin'])->group(function () {

    // Auth / account
    Route::post('/logout', [AdminAuthController::class, 'logout']);
    Route::get('/me', [AdminAuthController::class, 'me']);
    Route::post('/change-password', [AdminAuthController::class, 'changePassword']);
    Route::post('/admins', [AdminAuthController::class, 'store']); // super only (checked in controller)

    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index']);

    // App settings
    Route::get('/settings', [AdminAppController::class, 'show']);
    Route::post('/settings', [AdminAppController::class, 'update']);

    // Currencies
    Route::get('/currencies', [AdminCurrencyController::class, 'index']);
    Route::post('/currencies', [AdminCurrencyController::class, 'store']);
    Route::post('/currencies/{currency}', [AdminCurrencyController::class, 'update']);
    Route::delete('/currencies/{currency}', [AdminCurrencyController::class, 'destroy']);

    // Users
    Route::get('/users', [AdminUserController::class, 'index']);
    Route::get('/users/{user}', [AdminUserController::class, 'show']);
    Route::post('/users/{user}/ban', [AdminUserController::class, 'toggleBan']);
    Route::post('/users/{user}/activate', [AdminUserController::class, 'manualActivate']);
    Route::post('/users/{user}/balance', [AdminUserController::class, 'adjustBalance']);
    Route::get('/users/{user}/transactions', [AdminUserController::class, 'transactions']);
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy']);

    // Deposits
    Route::get('/deposits', [AdminDepositController::class, 'index']);
    Route::post('/deposits/{deposit}/approve', [AdminDepositController::class, 'approve']);
    Route::post('/deposits/{deposit}/reject', [AdminDepositController::class, 'reject']);

    // Withdrawals
    Route::get('/withdrawals', [AdminWithdrawalController::class, 'index']);
    Route::get('/withdrawals/{withdrawal}', [AdminWithdrawalController::class, 'show']);
    Route::post('/withdrawals/{withdrawal}/paid', [AdminWithdrawalController::class, 'markPaid']);
    Route::post('/withdrawals/{withdrawal}/reject', [AdminWithdrawalController::class, 'reject']);

    // Tasks (moderation)
    Route::get('/tasks', [AdminTaskController::class, 'index']);
    Route::get('/tasks/{task}', [AdminTaskController::class, 'show']);
    Route::post('/tasks/{task}/approve', [AdminTaskController::class, 'approve']);
    Route::post('/tasks/{task}/reject', [AdminTaskController::class, 'reject']);
    Route::delete('/tasks/{task}', [AdminTaskController::class, 'destroy']);

    // Categories
    Route::get('/categories', [AdminCategoryController::class, 'index']);
    Route::post('/categories', [AdminCategoryController::class, 'store']);
    Route::post('/categories/{category}', [AdminCategoryController::class, 'update']);
    Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy']);

    // Complaints
    Route::get('/complaints', [AdminComplaintController::class, 'index']);
    Route::get('/complaints/{complaint}', [AdminComplaintController::class, 'show']);
    Route::post('/complaints/{complaint}/resolve', [AdminComplaintController::class, 'resolve']);

    // Messages
    Route::get('/messages', [AdminMessageController::class, 'index']);
    Route::get('/messages/{user}', [AdminMessageController::class, 'show']);
    Route::post('/messages/{user}', [AdminMessageController::class, 'reply']);

    // FAQs
    Route::get('/faqs', [AdminFaqController::class, 'index']);
    Route::post('/faqs', [AdminFaqController::class, 'store']);
    Route::post('/faqs/{faq}', [AdminFaqController::class, 'update']);
    Route::delete('/faqs/{faq}', [AdminFaqController::class, 'destroy']);

    // Payment methods
    Route::get('/deposit-methods', [AdminMethodController::class, 'depositMethods']);
    Route::post('/deposit-methods', [AdminMethodController::class, 'storeDepositMethod']);
    Route::post('/deposit-methods/{method}', [AdminMethodController::class, 'updateDepositMethod']);
    Route::delete('/deposit-methods/{method}', [AdminMethodController::class, 'destroyDepositMethod']);
    Route::get('/withdrawal-methods', [AdminMethodController::class, 'withdrawalMethods']);
    Route::post('/withdrawal-methods', [AdminMethodController::class, 'storeWithdrawalMethod']);
    Route::post('/withdrawal-methods/{method}', [AdminMethodController::class, 'updateWithdrawalMethod']);
    Route::delete('/withdrawal-methods/{method}', [AdminMethodController::class, 'destroyWithdrawalMethod']);

    // Transactions ledger
    Route::get('/transactions', [AdminTransactionController::class, 'index']);
    Route::get('/transactions/{transaction}', [AdminTransactionController::class, 'show']);

    // Affiliate oversight
    Route::get('/affiliate/stats', [AdminAffiliateController::class, 'stats']);
    Route::get('/affiliate', [AdminAffiliateController::class, 'index']);
    Route::post('/affiliate/{referral}/revoke', [AdminAffiliateController::class, 'revoke']);
});
