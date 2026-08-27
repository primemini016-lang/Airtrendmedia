<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SponsoredAd;
use App\Models\SponsoredAdClick;
use App\Models\SponsoredAdImpression;
use App\Services\SettingService;
use Illuminate\Http\Request;

class SponsoredAdController extends Controller
{
    public function __construct(
        private SettingService $settings,
    ) {}

    /**
     * Advertiser dashboard: list their ads + create form.
     */
    public function index()
    {
        $user = auth('web')->user();
        $ads = $user->sponsoredAds()->latest()->paginate(10);
        $defaultCpc = (float) $this->settings->get('sponsored_ad_cpc', 0.02);
        $autoApprove = (bool) $this->settings->get('sponsored_ad_auto_approval', false);

        return view('user.sponsored-ads', compact('ads', 'defaultCpc', 'autoApprove'));
    }

    /**
     * Create a new sponsored ad. Deducts budget from balance.
     */
    public function store(Request $request)
    {
        $user = auth('web')->user();

        if (!$user->isAdvertiser()) {
            return back()->with('error', 'Only advertiser accounts can create sponsored ads. Switch your account type first.');
        }

        $defaultCpc = (float) $this->settings->get('sponsored_ad_cpc', 0.02);

        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:500'],
            'link_url'    => ['required', 'url', 'max:500'],
            'ad_type'     => ['required', 'in:text,image,video'],
            'media'       => ['nullable', 'file', 'max:25600'],
            'cta_button'  => ['nullable', 'string', 'max:30'],
            'budget'      => ['required', 'numeric', 'min:1'],
            'cost_per_click' => ['nullable', 'numeric', 'min:0.01'],
            'starts_at'   => ['nullable', 'date'],
            'ends_at'     => ['nullable', 'date', 'after:starts_at'],
        ]);

        $budget = (float) $validated['budget'];
        if ((float) $user->balance < $budget) {
            return back()->with('error', "Insufficient balance. You need \${$budget} for this ad budget.");
        }

        // Deduct budget immediately (held for the ad)
        $user->decrement('balance', $budget);

        $data = [
            'advertiser_id'    => $user->id,
            'title'            => $validated['title'],
            'description'      => $validated['description'],
            'link_url'         => $validated['link_url'],
            'ad_type'          => $validated['ad_type'],
            'cta_button'       => $validated['cta_button'] ?? 'Learn More',
            'cost_per_click'   => $validated['cost_per_click'] ?? $defaultCpc,
            'budget'           => $budget,
            'amount_spent'     => 0,
            'impressions'      => 0,
            'clicks'           => 0,
            'views'            => 0,
            'starts_at'        => $validated['starts_at'] ?? now(),
            'ends_at'          => $validated['ends_at'] ?? null,
        ];

        if ($request->hasFile('media')) {
            $data['media_path'] = $request->file('media')->store('sponsored-ads', 'public');
        }

        $autoApprove = (bool) $this->settings->get('sponsored_ad_auto_approval', false);
        $data['status'] = $autoApprove ? 'approved' : 'pending_review';

        $ad = SponsoredAd::create($data);

        if ($autoApprove) {
            return redirect()->route('user.sponsored-ads')->with('success', 'Sponsored ad created and auto-approved! It is now live in the feed.');
        }

        return redirect()->route('user.sponsored-ads')->with('success', 'Sponsored ad created and submitted for admin review.');
    }

    /**
     * Advertiser: view stats for a single ad.
     */
    public function stats(SponsoredAd $ad)
    {
        $user = auth('web')->user();
        if ($ad->advertiser_id !== $user->id) {
            abort(403);
        }

        $recentClicks = $ad->clicks()->with('user:id,username,name')->latest()->limit(20)->get();

        return view('user.sponsored-ad-stats', compact('ad', 'recentClicks'));
    }

    /**
     * Record an impression (anti-cheat: one per user per ad per day).
     */
    public function impression(Request $request, SponsoredAd $ad)
    {
        if (!$ad->isLive()) {
            return response()->json(['error' => 'Ad not live'], 404);
        }

        $user = auth('web')->user();
        $ip = $request->ip();

        $already = SponsoredAdImpression::where('sponsored_ad_id', $ad->id)
            ->where(function ($q) use ($user, $ip) {
                $q->where('user_id', $user->id)->orWhere('ip_address', $ip);
            })
            ->whereDate('created_at', today())
            ->exists();

        if (!$already) {
            SponsoredAdImpression::create([
                'sponsored_ad_id' => $ad->id,
                'user_id'         => $user->id,
                'ip_address'      => $ip,
            ]);
            $ad->increment('impressions');
        }

        return response()->json(['success' => true]);
    }

    /**
     * Record a click and deduct from ad budget.
     */
    public function click(Request $request, SponsoredAd $ad)
    {
        if (!$ad->isLive()) {
            return response()->json(['error' => 'Ad not live'], 404);
        }

        $user = auth('web')->user();
        $ip = $request->ip();
        $cpc = (float) $ad->cost_per_click;

        // Anti-cheat: prevent click fraud (one click per user per ad per day)
        $alreadyClicked = SponsoredAdClick::where('sponsored_ad_id', $ad->id)
            ->where(function ($q) use ($user, $ip) {
                $q->where('user_id', $user->id)->orWhere('ip_address', $ip);
            })
            ->whereDate('created_at', today())
            ->exists();

        if ($alreadyClicked) {
            return response()->json(['success' => false, 'message' => 'Already counted']);
        }

        // Only charge if budget remains
        if ($ad->remainingBudget() < $cpc) {
            $ad->update(['status' => 'budget_exhausted']);
            return response()->json(['error' => 'Budget exhausted'], 410);
        }

        SponsoredAdClick::create([
            'sponsored_ad_id' => $ad->id,
            'user_id'         => $user->id,
            'ip_address'      => $ip,
            'user_agent'      => $request->userAgent(),
            'cost'            => $cpc,
        ]);

        $ad->increment('clicks');
        $ad->increment('amount_spent', $cpc);

        // Auto-pause if budget exhausted
        if ($ad->fresh()->remainingBudget() <= 0) {
            $ad->fresh()->update(['status' => 'budget_exhausted']);
        }

        return response()->json(['success' => true, 'redirect' => $ad->link_url]);
    }

    /**
     * Get live approved ads for feed injection.
     */
    public function feedAds()
    {
        $ads = SponsoredAd::with('advertiser:id,username,name,image')
            ->where('status', 'approved')
            ->where('amount_spent', '<', \DB::raw('budget'))
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>', now());
            })
            ->inRandomOrder()
            ->limit(3)
            ->get();

        return response()->json(['ads' => $ads->map(fn ($a) => [
            'id'          => $a->id,
            'title'       => $a->title,
            'description' => $a->description,
            'link_url'    => $a->link_url,
            'ad_type'     => $a->ad_type,
            'media_url'   => $a->mediaUrl(),
            'cta_button'  => $a->cta_button,
            'advertiser'  => $a->advertiser?->name,
            'is_sponsored'=> true,
        ])]);
    }

    /* ===== Admin management ===== */

    public function adminIndex(Request $request)
    {
        $query = SponsoredAd::with('advertiser:id,username,name,email');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $ads = $query->latest()->paginate(20);
        $autoApprove = (bool) $this->settings->get('sponsored_ad_auto_approval', false);
        $defaultCpc = (float) $this->settings->get('sponsored_ad_cpc', 0.02);

        return view('admin.sponsored-ads', compact('ads', 'autoApprove', 'defaultCpc'));
    }

    public function adminApprove(SponsoredAd $ad)
    {
        $ad->update(['status' => 'approved', 'reviewed_at' => now()]);
        return back()->with('success', 'Sponsored ad approved and is now live.');
    }

    public function adminReject(Request $request, SponsoredAd $ad)
    {
        $validated = $request->validate(['rejection_reason' => 'required|string|max:500']);
        $ad->update([
            'status'           => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'reviewed_at'      => now(),
        ]);
        // Refund remaining budget
        $refund = $ad->remainingBudget();
        if ($refund > 0) {
            $ad->advertiser->increment('balance', $refund);
        }
        return back()->with('success', "Sponsored ad rejected. \${$refund} refunded to advertiser.");
    }

    public function adminExtendCpc(Request $request, SponsoredAd $ad)
    {
        $validated = $request->validate([
            'cost_per_click' => ['required', 'numeric', 'min:0.01'],
            'budget'         => ['nullable', 'numeric', 'min:0'],
        ]);

        $ad->update(['cost_per_click' => $validated['cost_per_click']]);

        if (!empty($validated['budget']) && $validated['budget'] > (float)$ad->budget) {
            $ad->update(['budget' => $validated['budget']]);
        }

        return back()->with('success', 'Ad CPC/budget updated.');
    }

    public function adminPause(SponsoredAd $ad)
    {
        $ad->update(['status' => 'paused']);
        return back()->with('success', 'Ad paused.');
    }

    public function adminResume(SponsoredAd $ad)
    {
        if ($ad->remainingBudget() > 0) {
            $ad->update(['status' => 'approved']);
            return back()->with('success', 'Ad resumed.');
        }
        return back()->with('error', 'Cannot resume — budget exhausted.');
    }

    public function adminSettings(Request $request)
    {
        $validated = $request->validate([
            'sponsored_ad_cpc'           => ['required', 'numeric', 'min:0.01'],
            'sponsored_ad_auto_approval' => ['nullable', 'boolean'],
            'sponsored_ad_enabled'       => ['nullable', 'boolean'],
        ]);

        $s = $this->settings->all();
        $s->update([
            'sponsored_ad_cpc'           => $validated['sponsored_ad_cpc'],
            'sponsored_ad_auto_approval' => $request->boolean('sponsored_ad_auto_approval'),
            'sponsored_ad_enabled'       => $request->boolean('sponsored_ad_enabled'),
        ]);
        $this->settings->flush();

        return back()->with('success', 'Sponsored ad settings updated.');
    }
}
