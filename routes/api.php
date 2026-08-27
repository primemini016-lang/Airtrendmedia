<?php

use App\Http\Controllers\Api\AffiliateController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DepositController;
use App\Http\Controllers\Api\HelperController;
use App\Http\Controllers\Api\OfferController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WithdrawalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| User API Routes  (prefix: /api)
|--------------------------------------------------------------------------
*/

// Public (no auth) -------------------------------------------------------
Route::get('/site-info', [HelperController::class, 'siteInfo']);
Route::get('/countries', [HelperController::class, 'countries']);
Route::get('/faqs', [HelperController::class, 'faqs']);
Route::get('/contact', [HelperController::class, 'contactDetails']);
Route::get('/paystack-key', [HelperController::class, 'paystackKey']);
Route::get('/categories', [CategoryController::class, 'index']);

// Auth -------------------------------------------------------------------
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify', [AuthController::class, 'verifyCode']);
Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
Route::post('/forget-otp', [AuthController::class, 'forgetOtp']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Paystack public callback (server-side verification)
Route::get('/payment/callback', [PaymentController::class, 'callback']);

// Authenticated user -----------------------------------------------------
Route::middleware('jwt.auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    // Profile & dashboard
    Route::get('/dashboard', [UserController::class, 'dashboard']);
    Route::get('/user', [UserController::class, 'me']);
    Route::post('/user/update', [UserController::class, 'update']);
    Route::post('/user/image', [UserController::class, 'updateImage']);
    Route::get('/users/{user}/profile', [UserController::class, 'publicProfile']);
    Route::get('/transactions', [UserController::class, 'transactions']);
    Route::get('/messages', [UserController::class, 'messages']);
    Route::post('/messages', [UserController::class, 'sendMessage']);
    Route::post('/messages/seen', [UserController::class, 'markMessagesSeen']);

    // Activation payment ($5) — requires verified email but not active account
    Route::post('/activation/initiate', [PaymentController::class, 'initiateActivation']);
    Route::post('/activation/verify', [PaymentController::class, 'verifyActivation']);

    // Wallet deposit (Paystack)
    Route::post('/deposit/initiate', [PaymentController::class, 'initiateDeposit']);
    Route::post('/deposit/verify', [PaymentController::class, 'verifyDeposit']);

    // Manual deposit methods + history
    Route::get('/deposit/methods', [DepositController::class, 'methods']);
    Route::post('/deposit/manual', [DepositController::class, 'manualStore']);
    Route::get('/deposits', [DepositController::class, 'index']);

    // Withdrawals
    Route::get('/withdrawal/methods', [WithdrawalController::class, 'methods']);
    Route::post('/withdrawal', [WithdrawalController::class, 'store']);
    Route::get('/withdrawals', [WithdrawalController::class, 'index']);

    // Affiliate program (statistics available even pre-activation)
    Route::get('/affiliate/stats', [AffiliateController::class, 'stats']);
    Route::get('/affiliate/summary', [AffiliateController::class, 'summary']);
    Route::get('/affiliate/referrals', [AffiliateController::class, 'referrals']);

    // Routes that require an active (paid) account -----------------------
    Route::middleware('active')->group(function () {
        // Browse & perform tasks (worker)
        Route::get('/tasks', [TaskController::class, 'index']);
        Route::get('/tasks/{task}', [TaskController::class, 'show']);
        Route::post('/tasks/{task}/book', [TaskController::class, 'book']);
        Route::post('/tasks/{task}/proof', [TaskController::class, 'submitProof']);
        Route::post('/tasks/{task}/report', [TaskController::class, 'report']);
        Route::get('/my-bookings', [UserController::class, 'bookings']);

        // Employer (offers)
        Route::get('/my-tasks', [OfferController::class, 'myTasks']);
        Route::post('/tasks', [OfferController::class, 'store']);
        Route::get('/tasks/{task}/proofs', [OfferController::class, 'proofs']);
        Route::post('/proofs/{proof}/approve', [OfferController::class, 'approveProof']);
        Route::post('/proofs/{proof}/reject', [OfferController::class, 'rejectProof']);
    });
});
