<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PtcAd;
use App\Models\PtcView;
use App\Models\SiteSetting;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PtcController extends Controller
{
    /* ===== Constants from the product spec =====
     * Each 10 seconds of PTC execution = $0.005 reward.
     * Minimum execution time: 10 seconds. Maximum: 5 hours (18 000 s).
     * Minimum cost per view (advertiser pays): $0.005.
     * Users can view unlimited PTC ads per day.
     */
    public const MIN_DURATION_SECONDS = 10;
    public const MAX_DURATION_SECONDS = 18000; // 5 hours
    public const SECONDS_PER_UNIT     = 10;
    public const REWARD_PER_UNIT      = 0.005;
    public const MIN_COST_PER_VIEW    = 0.005;

    /* -----------------------------------------------------------------
     |  Public browse
     |----------------------------------------------------------------- */
    public function index(Request $request)
    {
        $ads = PtcAd::where('status', 'approved')
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->where(function ($q) {
                $q->where('max_views', 0)->orWhereColumn('views_count', '<', 'max_views');
            })
            ->latest('approved_at')
            ->paginate(24);

        $totalAds   = $ads->total();
        $totalPaid  = Transaction::where('type', 'ptc_reward')->sum('amount');
        $todayCount = 0;

        if (Auth::guard('web')->check()) {
            $todayCount = PtcView::where('user_id', Auth::guard('web')->id())
                ->where('status', 'confirmed')
                ->whereDate('confirmed_at', today())
                ->count();
        }

        return view('ptc.index', compact('ads', 'totalAds', 'totalPaid', 'todayCount'));
    }

    public function show(PtcAd $ad)
    {
        $ad->increment('views_count');
        $alreadyViewed = false;

        if (Auth::guard('web')->check()) {
            $alreadyViewed = PtcView::where('ptc_ad_id', $ad->id)
                ->where('user_id', Auth::guard('web')->id())
                ->where('status', 'confirmed')
                ->whereDate('confirmed_at', today())
                ->exists();
        }

        return view('ptc.show', compact('ad', 'alreadyViewed'));
    }

    /* -----------------------------------------------------------------
     |  User: my PTC ads (CRUD + stats)
     |----------------------------------------------------------------- */
    public function myAds(Request $request)
    {
        $ads = PtcAd::where('user_id', Auth::guard('web')->id())
            ->latest()
            ->paginate(15);

        return view('user.ptc.index', compact('ads'));
    }

    public function create()
    {
        $minDuration = self::MIN_DURATION_SECONDS;
        $maxDuration = self::MAX_DURATION_SECONDS;
        $minCost     = self::MIN_COST_PER_VIEW;

        return view('user.ptc.create', compact('minDuration', 'maxDuration', 'minCost'));
    }

    public function store(Request $request)
    {
        $minCost = (float) SiteSetting::get('ptc_min_cost_per_view', self::MIN_COST_PER_VIEW);

        $validated = $request->validate([
            'title'           => ['required', 'string', 'max:120'],
            'url'             => ['nullable', 'url', 'max:500'],
            'description'     => ['nullable', 'string', 'max:5000'],
            'image'           => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp,bmp,svg', 'max:5120'],
            'duration_seconds'=> ['required', 'integer', 'min:' . self::MIN_DURATION_SECONDS, 'max:' . self::MAX_DURATION_SECONDS],
            'cost_per_view'   => ['required', 'numeric', 'min:' . $minCost],
            'max_views'       => ['required', 'integer', 'min:1', 'max:1000000'],
            'mode'            => ['required', 'in:automatic,manual'],
            'starts_at'       => ['nullable', 'date', 'after:now'],
            'ends_at'         => ['nullable', 'date', 'after:starts_at'],
        ]);

        $duration = (int) $validated['duration_seconds'];
        $units    = intdiv($duration, self::SECONDS_PER_UNIT);
        // reward = units * $0.005 ; cost is what the advertiser pays (>= reward, min spec price)
        $reward   = round($units * self::REWARD_PER_UNIT, 4);
        $cost     = max((float) $validated['cost_per_view'], $reward, $minCost);

        $totalCost = round($cost * (int) $validated['max_views'], 2);

        $user = Auth::guard('web')->user();
        if ($user->balance < $totalCost) {
            return back()->withErrors(['cost_per_view' => "Insufficient balance. This campaign needs \${$totalCost}. Your balance is \${$user->balance}."])->withInput();
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('ptc', 'public');
        }

        DB::transaction(function () use ($user, $validated, $duration, $reward, $cost, $totalCost, $imagePath) {
            $user->decrement('balance', $totalCost);

            Transaction::create([
                'user_id'       => $user->id,
                'type'          => 'ptc_ad_purchase',
                'reference'     => 'PTC-' . strtoupper(uniqid()),
                'amount'        => -$totalCost,
                'balance_after' => $user->fresh()->balance,
                'currency'      => 'USD',
                'status'        => 'completed',
                'description'   => "PTC ad campaign: {$validated['title']}",
            ]);

            PtcAd::create([
                'user_id'         => $user->id,
                'title'           => $validated['title'],
                'url'             => $validated['url'] ?? null,
                'description'     => $validated['description'] ?? null,
                'image'           => $imagePath,
                'duration_seconds'=> $duration,
                'reward_per_view' => $reward,
                'cost_per_view'   => $cost,
                'budget'          => $totalCost,
                'max_views'       => $validated['max_views'],
                'status'          => 'pending',
                'mode'            => $validated['mode'],
                'starts_at'       => $validated['starts_at'] ?? null,
                'ends_at'         => $validated['ends_at'] ?? null,
            ]);
        });

        return redirect()->route('user.ptc.index')->with('success', 'PTC ad submitted for admin approval.');
    }

    public function edit(PtcAd $ad)
    {
        if ($ad->user_id !== Auth::guard('web')->id()) {
            abort(403);
        }

        $minDuration = self::MIN_DURATION_SECONDS;
        $maxDuration = self::MAX_DURATION_SECONDS;
        $minCost     = self::MIN_COST_PER_VIEW;

        return view('user.ptc.edit', compact('ad', 'minDuration', 'maxDuration', 'minCost'));
    }

    public function update(Request $request, PtcAd $ad)
    {
        if ($ad->user_id !== Auth::guard('web')->id()) {
            abort(403);
        }
        if (in_array($ad->status, ['approved', 'completed'])) {
            return back()->withErrors(['status' => 'Approved or completed ads cannot be edited.']);
        }

        $validated = $request->validate([
            'title'           => ['required', 'string', 'max:120'],
            'url'             => ['nullable', 'url', 'max:500'],
            'description'     => ['nullable', 'string', 'max:5000'],
            'image'           => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp,bmp,svg', 'max:5120'],
            'duration_seconds'=> ['required', 'integer', 'min:' . self::MIN_DURATION_SECONDS, 'max:' . self::MAX_DURATION_SECONDS],
            'mode'            => ['required', 'in:automatic,manual'],
            'starts_at'       => ['nullable', 'date'],
            'ends_at'         => ['nullable', 'date', 'after:starts_at'],
        ]);

        $duration = (int) $validated['duration_seconds'];
        $units    = intdiv($duration, self::SECONDS_PER_UNIT);
        $reward   = round($units * self::REWARD_PER_UNIT, 4);

        $data = $validated;
        $data['reward_per_view'] = $reward;
        $data['status']          = 'pending'; // re-approval required after edit

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('ptc', 'public');
        }

        $ad->update($data);

        return redirect()->route('user.ptc.index')->with('success', 'PTC ad updated and sent back for approval.');
    }

    public function destroy(PtcAd $ad)
    {
        if ($ad->user_id !== Auth::guard('web')->id()) {
            abort(403);
        }
        $ad->delete();

        return redirect()->route('user.ptc.index')->with('success', 'PTC ad deleted.');
    }

    public function stats(PtcAd $ad)
    {
        if ($ad->user_id !== Auth::guard('web')->id()) {
            abort(403);
        }

        $stats = [
            'total_views'    => $ad->views()->count(),
            'confirmed_views'=> $ad->views()->where('status', 'confirmed')->count(),
            'today_views'    => $ad->views()->where('status', 'confirmed')->whereDate('confirmed_at', today())->count(),
            'total_spent'    => $ad->views()->where('status', 'confirmed')->sum('reward'),
            'remaining'      => $ad->max_views > 0 ? max(0, $ad->max_views - $ad->views_count) : 'Unlimited',
        ];

        return view('user.ptc.stats', compact('ad', 'stats'));
    }

    /* -----------------------------------------------------------------
     |  User: view + confirm execution (instant reward)
     |----------------------------------------------------------------- */
    public function startView(Request $request, PtcAd $ad)
    {
        if (! $ad->isLive()) {
            return response()->json(['error' => 'This ad is not currently active.'], 422);
        }

        $userId = Auth::guard('web')->id();

        // Prevent duplicate confirmed view on the same ad same day (anti-cheat),
        // but allow unlimited ads per day.
        $existing = PtcView::where('ptc_ad_id', $ad->id)
            ->where('user_id', $userId)
            ->where('status', 'confirmed')
            ->whereDate('confirmed_at', today())
            ->first();

        if ($existing) {
            return response()->json(['error' => 'You have already completed this ad today.'], 422);
        }

        $view = PtcView::create([
            'ptc_ad_id'      => $ad->id,
            'user_id'        => $userId,
            'reward'         => $ad->reward_per_view,
            'watched_seconds'=> 0,
            'ip_address'     => $request->ip(),
            'status'         => 'started',
            'started_at'     => now(),
        ]);

        return response()->json([
            'view_id'        => $view->id,
            'duration'       => $ad->duration_seconds,
            'reward'         => (float) $ad->reward_per_view,
        ]);
    }

    public function confirmExecution(Request $request, PtcAd $ad)
    {
        $validated = $request->validate([
            'view_id'         => ['required', 'integer', 'exists:ptc_views,id'],
            'watched_seconds' => ['required', 'integer', 'min:' . self::MIN_DURATION_SECONDS],
        ]);

        $userId = Auth::guard('web')->id();
        $view   = PtcView::where('id', $validated['view_id'])
            ->where('ptc_ad_id', $ad->id)
            ->where('user_id', $userId)
            ->where('status', 'started')
            ->first();

        if (! $view) {
            return response()->json(['error' => 'Invalid or expired viewing session.'], 422);
        }

        // Anti-cheat: ensure the user actually waited the required duration.
        $required = $ad->duration_seconds;
        $watched  = (int) $validated['watched_seconds'];
        if ($watched < $required) {
            return response()->json(['error' => 'You must watch the ad for the full duration.'], 422);
        }

        $reward = (float) $ad->reward_per_view;
        $user   = Auth::guard('web')->user();

        DB::transaction(function () use ($view, $ad, $reward, $user) {
            $view->update([
                'status'         => 'confirmed',
                'watched_seconds'=> $ad->duration_seconds,
                'confirmed_at'   => now(),
            ]);

            $user->increment('balance', $reward);
            $user->increment('total_earned', $reward);

            Transaction::create([
                'user_id'       => $user->id,
                'type'          => 'ptc_reward',
                'reference'     => 'PTCR-' . strtoupper(uniqid()),
                'amount'        => $reward,
                'balance_after' => $user->fresh()->balance,
                'currency'      => 'USD',
                'status'        => 'completed',
                'description'   => "PTC reward for watching: {$ad->title}",
                'related_id'    => $ad->id,
                'related_type'  => PtcAd::class,
            ]);

            $ad->increment('views_count');
        });

        return response()->json([
            'success'     => true,
            'reward'      => $reward,
            'new_balance' => (float) $user->fresh()->balance,
            'message'     => "Reward of $" . number_format($reward, 4) . " credited to your balance!",
        ]);
    }

    public function history(Request $request)
    {
        $views = PtcView::where('user_id', Auth::guard('web')->id())
            ->with('ad:id,title,image')
            ->where('status', 'confirmed')
            ->latest('confirmed_at')
            ->paginate(25);

        $todayEarnings = PtcView::where('user_id', Auth::guard('web')->id())
            ->where('status', 'confirmed')
            ->whereDate('confirmed_at', today())
            ->sum('reward');

        $totalEarnings = PtcView::where('user_id', Auth::guard('web')->id())
            ->where('status', 'confirmed')
            ->sum('reward');

        return view('user.ptc.history', compact('views', 'todayEarnings', 'totalEarnings'));
    }

    /* -----------------------------------------------------------------
     |  Admin: God-mode PTC management
     |----------------------------------------------------------------- */
    public function adminIndex(Request $request)
    {
        $query = PtcAd::with('user:id,username,name,email');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $ads = $query->latest()->paginate(25);

        $counts = [
            'pending'   => PtcAd::where('status', 'pending')->count(),
            'approved'  => PtcAd::where('status', 'approved')->count(),
            'paused'    => PtcAd::where('status', 'paused')->count(),
            'rejected'  => PtcAd::where('status', 'rejected')->count(),
            'completed' => PtcAd::where('status', 'completed')->count(),
        ];

        return view('admin.ptc.index', compact('ads', 'counts'));
    }

    public function adminShow(PtcAd $ad)
    {
        $ad->load('user', 'views.user:id,username,name');
        $recentViews = $ad->views()->latest()->limit(50)->get();

        return view('admin.ptc.show', compact('ad', 'recentViews'));
    }

    public function adminApprove(PtcAd $ad)
    {
        $ad->update([
            'status'      => 'approved',
            'approved_at' => now(),
            'admin_note'  => null,
        ]);

        return back()->with('success', "PTC ad \"{$ad->title}\" approved and is now live.");
    }

    public function adminReject(Request $request, PtcAd $ad)
    {
        $ad->update([
            'status'     => 'rejected',
            'admin_note' => $request->input('reason', 'Rejected by admin.'),
        ]);

        return back()->with('success', "PTC ad \"{$ad->title}\" rejected.");
    }

    public function adminPause(PtcAd $ad)
    {
        $ad->update(['status' => 'paused']);
        return back()->with('success', "PTC ad \"{$ad->title}\" paused.");
    }

    public function adminResume(PtcAd $ad)
    {
        $ad->update(['status' => 'approved']);
        return back()->with('success', "PTC ad \"{$ad->title}\" resumed.");
    }

    public function adminDestroy(PtcAd $ad)
    {
        $ad->delete();
        return back()->with('success', 'PTC ad deleted permanently.');
    }

    public function adminSettings(Request $request)
    {
        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'ptc_min_cost_per_view'  => ['required', 'numeric', 'min:0.005'],
                'ptc_min_duration'       => ['required', 'integer', 'min:10'],
                'ptc_max_duration'       => ['required', 'integer', 'min:10', 'max:18000'],
                'ptc_reward_per_unit'    => ['required', 'numeric', 'min:0.001'],
                'ptc_seconds_per_unit'   => ['required', 'integer', 'min:1'],
                'ptc_enabled'            => ['nullable', 'in:1,0'],
                'ptc_auto_approve'       => ['nullable', 'in:1,0'],
            ]);

            foreach ($validated as $key => $value) {
                SiteSetting::set($key, $value, 'ptc');
            }

            return back()->with('success', 'PTC settings updated.');
        }

        $settings = [
            'ptc_min_cost_per_view' => SiteSetting::get('ptc_min_cost_per_view', self::MIN_COST_PER_VIEW),
            'ptc_min_duration'      => SiteSetting::get('ptc_min_duration', self::MIN_DURATION_SECONDS),
            'ptc_max_duration'      => SiteSetting::get('ptc_max_duration', self::MAX_DURATION_SECONDS),
            'ptc_reward_per_unit'   => SiteSetting::get('ptc_reward_per_unit', self::REWARD_PER_UNIT),
            'ptc_seconds_per_unit'  => SiteSetting::get('ptc_seconds_per_unit', self::SECONDS_PER_UNIT),
            'ptc_enabled'           => SiteSetting::get('ptc_enabled', '1'),
            'ptc_auto_approve'      => SiteSetting::get('ptc_auto_approve', '0'),
        ];

        return view('admin.ptc.settings', compact('settings'));
    }
}
