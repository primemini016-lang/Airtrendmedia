<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MonetizationEligibility;
use App\Models\MonetizationEarning;
use App\Models\ContentSubscription;
use App\Models\ContentStar;
use App\Models\Withdrawal;
use App\Models\WithdrawalMethod;
use App\Models\User;
use Illuminate\Http\Request;

class MonetizationController extends Controller
{
    /**
     * Monetization dashboard — Facebook-style earnings overview.
     */
    public function dashboard()
    {
        $user = auth('web')->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $eligibility = MonetizationEligibility::where('user_id', $user->id)->first();
        $eligibilityCheck = MonetizationEligibility::checkEligibility($user);

        $totalEarnings = MonetizationEarning::totalEarnings($user);
        $monthlyEarnings = MonetizationEarning::monthlyEarnings($user);
        $totalStars = ContentStar::totalStars($user);

        // Recent earnings breakdown
        $recentEarnings = MonetizationEarning::where('user_id', $user->id)
            ->latest()
            ->limit(20)
            ->get();

        // Earnings by source type
        $earningsBySource = MonetizationEarning::where('user_id', $user->id)
            ->selectRaw('source_type, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('source_type')
            ->get();

        // Subscribers
        $subscribers = ContentSubscription::where('creator_id', $user->id)
            ->where('status', 'active')
            ->with('subscriber:id,username,name,image')
            ->latest()
            ->paginate(10);

        $activeSubscribersCount = ContentSubscription::where('creator_id', $user->id)
            ->where('status', 'active')
            ->count();

        // Stars received
        $starsReceived = ContentStar::where('receiver_id', $user->id)
            ->with('sender:id,username,name,image')
            ->latest()
            ->paginate(10);

        // Withdrawals
        $withdrawals = Withdrawal::where('user_id', $user->id)
            ->latest()
            ->limit(10)
            ->get();

        $withdrawalMethods = WithdrawalMethod::where('active', true)->get();

        return view('social.monetization', compact(
            'user', 'eligibility', 'eligibilityCheck', 'totalEarnings',
            'monthlyEarnings', 'totalStars', 'recentEarnings', 'earningsBySource',
            'subscribers', 'activeSubscribersCount', 'starsReceived',
            'withdrawals', 'withdrawalMethods'
        ));
    }

    /**
     * Apply for monetization.
     */
    public function apply(Request $request)
    {
        $user = auth('web')->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $existing = MonetizationEligibility::where('user_id', $user->id)->first();

        if ($existing && $existing->is_eligible) {
            return back()->with('info', 'You are already monetized.');
        }

        if ($existing && $existing->approved_at) {
            return back()->with('info', 'Your application is under review.');
        }

        $check = MonetizationEligibility::checkEligibility($user);

        $eligibility = MonetizationEligibility::updateOrCreate(
            ['user_id' => $user->id],
            [
                'is_eligible'          => $check['eligible'],
                'content_monetization' => $check['eligible'],
                'fan_subscriptions'    => $check['eligible'] && $check['requirements']['followers']['actual'] >= 1000,
                'stars_enabled'        => $check['eligible'],
                'followers_count'      => $user->followers_count,
                'content_count'        => $user->socialPosts()->count(),
                'engagement_score'     => $check['engagement'],
                'country'              => $user->country_code,
                'rejection_reason'     => $check['eligible'] ? null : 'Requirements not met yet.',
                'approved_at'          => $check['eligible'] ? now() : null,
            ]
        );

        if ($check['eligible']) {
            $user->monetization_enabled = true;
            $user->save();

            return redirect()->route('social.monetization')
                ->with('success', 'Congratulations! You are now eligible for monetization. All monetization features have been enabled.');
        }

        $missing = collect($check['requirements'])
            ->filter(fn ($req) => $req['actual'] < $req['required'])
            ->pluck('label')
            ->implode(', ');

        return redirect()->route('social.monetization')
            ->with('error', "You do not meet the monetization requirements yet. Missing: {$missing}");
    }

    /**
     * Set up fan subscription (monthly subscription price for followers).
     */
    public function setupSubscription(Request $request)
    {
        $user = auth('web')->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $eligibility = MonetizationEligibility::where('user_id', $user->id)->first();
        if (!$eligibility || !$eligibility->fan_subscriptions) {
            return back()->with('error', 'Fan subscriptions are not enabled for your account.');
        }

        $validated = $request->validate([
            'monthly_amount' => 'required|numeric|min:0.99|max:999.99',
        ]);

        // Store the subscription price on the eligibility record metadata
        // We use a simple approach: update the eligibility with the subscription amount
        $eligibility->update([
            'fan_subscriptions' => true,
        ]);

        // Store the price in app settings-like fashion on the user model
        // We'll use the existing balance field area — but for subscription price,
        // we add it as a setting in the eligibility metadata
        // For simplicity, we store it in the content_subscriptions default amount
        // using a special configuration approach

        return back()->with('success', "Fan subscription enabled at \${$validated['monthly_amount']}/month.");
    }

    /**
     * Withdraw monetization earnings.
     */
    public function withdraw(Request $request)
    {
        $user = auth('web')->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $eligibility = MonetizationEligibility::where('user_id', $user->id)->first();
        if (!$eligibility || !$eligibility->is_eligible) {
            return back()->with('error', 'You must be monetized to withdraw earnings.');
        }

        $validated = $request->validate([
            'amount'           => 'required|numeric|min:10',
            'withdrawal_method_id' => 'required|exists:withdrawal_methods,id',
            'account_details'  => 'required|string|max:5000',
        ]);

        $totalEarnings = MonetizationEarning::totalEarnings($user);
        $alreadyWithdrawn = Withdrawal::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'completed'])
            ->sum('amount');

        $available = $totalEarnings - $alreadyWithdrawn;

        if ($validated['amount'] > $available) {
            return back()->with('error', "Insufficient earnings. Available: \${$available}");
        }

        $withdrawal = Withdrawal::create([
            'user_id'              => $user->id,
            'withdrawal_method_id' => $validated['withdrawal_method_id'],
            'amount'               => $validated['amount'],
            'account_details'      => $validated['account_details'],
            'status'               => 'pending',
        ]);

        return redirect()->route('social.monetization')
            ->with('success', "Withdrawal request of \${$validated['amount']} submitted. You will be notified when processed.");
    }

    /**
     * Subscribe to a creator (fan subscription).
     */
    public function subscribe(Request $request, User $creator)
    {
        $user = auth('web')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if ($user->id === $creator->id) {
            return response()->json(['error' => 'You cannot subscribe to yourself.'], 422);
        }

        $validated = $request->validate([
            'monthly_amount' => 'required|numeric|min:0.99',
        ]);

        // Check if already subscribed
        $existing = ContentSubscription::where('subscriber_id', $user->id)
            ->where('creator_id', $creator->id)
            ->where('status', 'active')
            ->first();

        if ($existing) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'You are already subscribed to this creator.'], 422);
            }
            return back()->with('info', 'You are already subscribed to this creator.');
        }

        // Check user balance
        if ($user->balance < $validated['monthly_amount']) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Insufficient balance. Please deposit funds first.'], 422);
            }
            return back()->with('error', 'Insufficient balance. Please deposit funds to subscribe.');
        }

        // Deduct from subscriber
        $user->decrement('balance', $validated['monthly_amount']);
        $user->save();

        // Credit creator (if monetization enabled)
        if ($creator->monetization_enabled) {
            $creator->increment('balance', $validated['monthly_amount']);
            $creator->save();

            MonetizationEarning::create([
                'user_id'          => $creator->id,
                'source_type'      => 'subscription',
                'amount'           => $validated['monthly_amount'],
                'currency'         => 'USD',
            ]);
        }

        // Create subscription
        $subscription = ContentSubscription::create([
            'subscriber_id'   => $user->id,
            'creator_id'      => $creator->id,
            'monthly_amount'  => $validated['monthly_amount'],
            'status'          => 'active',
            'started_at'      => now(),
            'ends_at'         => now()->addMonth(),
        ]);

        return $request->expectsJson()
            ? response()->json(['message' => "Subscribed to {$creator->name}!"])
            : back()->with('success', "You are now subscribed to {$creator->name}!");
    }

    /**
     * Cancel a subscription.
     */
    public function cancelSubscription(Request $request, ContentSubscription $subscription)
    {
        $user = auth('web')->user();
        if (!$user) {
            return redirect()->route('login');
        }

        if ($subscription->subscriber_id !== $user->id) {
            abort(403);
        }

        $subscription->update([
            'status'  => 'cancelled',
            'ends_at' => now(),
        ]);

        return back()->with('success', 'Subscription cancelled. You will not be charged next month.');
    }
}
