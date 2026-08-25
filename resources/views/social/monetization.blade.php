@extends('layouts.social')

@section('title', 'Monetization — Airtrendmedia')

@section('content')
<div class="fb-main-container">
    <div class="fb-content-area">

        {{-- Page Header --}}
        <div class="fb-page-header">
            <h1 class="fb-page-title">
                <x-icon name="monetization" class="w-7 h-7 inline" />
                Monetization
            </h1>
            <p class="fb-page-subtitle">Earn money from your content, just like Facebook.</p>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="fb-alert fb-alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="fb-alert fb-alert-error">{{ session('error') }}</div>
        @endif
        @if(session('info'))
            <div class="fb-alert fb-alert-info">{{ session('info') }}</div>
        @endif

        {{-- Earnings Overview Cards --}}
        <div class="fb-stats-grid">
            <div class="fb-stat-card">
                <div class="fb-stat-icon fb-stat-blue"><x-icon name="monetization" class="w-6 h-6" /></div>
                <div class="fb-stat-value">${{ number_format($totalEarnings ?? 0, 2) }}</div>
                <div class="fb-stat-label">Total Earnings</div>
            </div>
            <div class="fb-stat-card">
                <div class="fb-stat-icon fb-stat-green"><x-icon name="trending-feed" class="w-6 h-6" /></div>
                <div class="fb-stat-value">${{ number_format($monthlyEarnings ?? 0, 2) }}</div>
                <div class="fb-stat-label">This Month</div>
            </div>
            <div class="fb-stat-card">
                <div class="fb-stat-icon fb-stat-yellow"><x-icon name="star" class="w-6 h-6" /></div>
                <div class="fb-stat-value">{{ number_format($totalStars ?? 0) }}</div>
                <div class="fb-stat-label">Stars Received</div>
            </div>
            <div class="fb-stat-card">
                <div class="fb-stat-icon fb-stat-purple"><x-icon name="user-group" class="w-6 h-6" /></div>
                <div class="fb-stat-value">{{ $activeSubscribersCount ?? 0 }}</div>
                <div class="fb-stat-label">Subscribers</div>
            </div>
        </div>

        {{-- Eligibility Section --}}
        <section class="fb-card fb-mono-card">
            <div class="fb-card-header">
                <h2 class="fb-card-title">
                    <x-icon name="verified" class="w-6 h-6 fb-primary-text" />
                    Monetization Eligibility
                </h2>
            </div>
            <div class="fb-card-body">
                @if($eligibility && $eligibility->is_eligible && $eligibility->approved_at)
                    <div class="fb-eligible-banner">
                        <x-icon name="check-circle" class="w-8 h-8" />
                        <div>
                            <h3 class="font-bold text-lg">You're Monetized! 🎉</h3>
                            <p class="text-sm">Your account is eligible for all monetization features.</p>
                        </div>
                    </div>

                    <div class="fb-mono-features">
                        <div class="fb-mono-feature {{ $eligibility->content_monetization ? 'enabled' : '' }}">
                            <x-icon name="news-feed" class="w-6 h-6" />
                            <div>
                                <div class="font-semibold">Content Monetization</div>
                                <div class="text-sm fb-text-muted">Earn from ads on your posts</div>
                            </div>
                            <span class="fb-mono-status">{{ $eligibility->content_monetization ? 'Active' : 'Inactive' }}</span>
                        </div>
                        <div class="fb-mono-feature {{ $eligibility->stars_enabled ? 'enabled' : '' }}">
                            <x-icon name="star" class="w-6 h-6" />
                            <div>
                                <div class="font-semibold">Stars</div>
                                <div class="text-sm fb-text-muted">Receive Stars from fans</div>
                            </div>
                            <span class="fb-mono-status">{{ $eligibility->stars_enabled ? 'Active' : 'Inactive' }}</span>
                        </div>
                        <div class="fb-mono-feature {{ $eligibility->fan_subscriptions ? 'enabled' : '' }}">
                            <x-icon name="user-group" class="w-6 h-6" />
                            <div>
                                <div class="font-semibold">Fan Subscriptions</div>
                                <div class="text-sm fb-text-muted">Monthly subscription from fans</div>
                            </div>
                            <span class="fb-mono-status">{{ $eligibility->fan_subscriptions ? 'Active' : 'Inactive' }}</span>
                        </div>
                    </div>
                @else
                    <div class="fb-not-eligible-banner">
                        <x-icon name="info" class="w-8 h-8" />
                        <div>
                            <h3 class="font-bold text-lg">Become Eligible for Monetization</h3>
                            <p class="text-sm">Meet the requirements below to start earning from your content.</p>
                        </div>
                    </div>

                    <div class="fb-requirements-list">
                        @php $reqs = $eligibilityCheck['requirements'] ?? []; @endphp
                        @foreach($reqs as $key => $req)
                            @php $met = $req['actual'] >= $req['required']; @endphp
                            <div class="fb-requirement-item {{ $met ? 'met' : 'unmet' }}">
                                <div class="fb-req-check">
                                    @if($met)
                                        <x-icon name="check-circle" class="w-6 h-6 text-green-500" />
                                    @else
                                        <x-icon name="x-circle" class="w-6 h-6 text-red-500" />
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <div class="font-semibold fb-text">{{ $req['label'] }}</div>
                                    <div class="text-sm fb-text-muted">
                                        You have {{ number_format($req['actual']) }} / need {{ number_format($req['required']) }}
                                    </div>
                                </div>
                                <div class="fb-req-progress">
                                    <div class="fb-req-progress-bar" style="width: {{ min(100, ($req['actual'] / max(1, $req['required'])) * 100) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <form action="{{ route('social.monetization.apply') }}" method="POST" class="mt-4">
                        @csrf
                        <button type="submit" class="fb-btn fb-btn-primary w-full">
                            <x-icon name="verified" class="w-5 h-5 inline" /> Check Eligibility & Apply
                        </button>
                    </form>
                @endif
            </div>
        </section>

        {{-- Fan Subscription Setup --}}
        @if($eligibility && $eligibility->is_eligible)
        <section class="fb-card fb-mono-card mt-4">
            <div class="fb-card-header">
                <h2 class="fb-card-title">
                    <x-icon name="user-group" class="w-6 h-6 fb-primary-text" />
                    Fan Subscription Setup
                </h2>
            </div>
            <div class="fb-card-body">
                <p class="fb-text-muted text-sm mb-4">Set a monthly price for fans to subscribe to your exclusive content.</p>
                <form action="{{ route('social.monetization.subscription') }}" method="POST" class="flex gap-3 items-end">
                    @csrf
                    <div class="flex-1">
                        <label class="fb-label">Monthly Subscription Price ($)</label>
                        <input type="number" name="monthly_amount" step="0.01" min="0.99" max="999.99"
                               value="4.99" class="fb-input" placeholder="4.99">
                    </div>
                    <button type="submit" class="fb-btn fb-btn-primary">Save</button>
                </form>
            </div>
        </section>
        @endif

        {{-- Two Column: Recent Earnings + Subscribers --}}
        <div class="fb-mono-grid">

            {{-- Recent Earnings --}}
            <section class="fb-card fb-mono-card">
                <div class="fb-card-header">
                    <h2 class="fb-card-title">
                        <x-icon name="clock-history" class="w-6 h-6 fb-primary-text" />
                        Recent Earnings
                    </h2>
                </div>
                <div class="fb-card-body">
                    @if($recentEarnings->isNotEmpty())
                        <div class="fb-earnings-list">
                            @foreach($recentEarnings as $earning)
                                <div class="fb-earning-item">
                                    <div class="fb-earning-info">
                                        <div class="fb-earning-source fb-text font-medium">
                                            {{ ucfirst(str_replace('_', ' ', $earning->source_type)) }}
                                        </div>
                                        <div class="fb-earning-time fb-text-muted text-xs">
                                            {{ $earning->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                    <div class="fb-earning-amount text-green-600 font-bold">
                                        +${{ number_format($earning->amount, 2) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="fb-empty-state">
                            <x-icon name="monetization" class="w-12 h-12 mx-auto mb-3 opacity-30" />
                            <p class="fb-text-muted">No earnings yet. Start creating content to earn!</p>
                        </div>
                    @endif
                </div>
            </section>

            {{-- Subscribers --}}
            <section class="fb-card fb-mono-card">
                <div class="fb-card-header">
                    <h2 class="fb-card-title">
                        <x-icon name="user-group" class="w-6 h-6 fb-primary-text" />
                        Active Subscribers ({{ $activeSubscribersCount }})
                    </h2>
                </div>
                <div class="fb-card-body">
                    @if($subscribers->isNotEmpty())
                        <div class="fb-subscribers-list">
                            @foreach($subscribers as $sub)
                                <div class="fb-subscriber-item">
                                    <img src="{{ $sub->subscriber?->avatarUrl() }}" class="fb-avatar fb-avatar-sm" alt="">
                                    <a href="{{ route('social.profile', $sub->subscriber?->username) }}" class="fb-text font-medium hover:underline">
                                        {{ $sub->subscriber?->name }}
                                    </a>
                                    <span class="fb-text-muted text-xs ml-auto">{{ $sub->created_at->diffForHumans() }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="fb-empty-state">
                            <x-icon name="user-group" class="w-12 h-12 mx-auto mb-3 opacity-30" />
                            <p class="fb-text-muted">No active subscribers yet.</p>
                        </div>
                    @endif
                </div>
            </section>
        </div>

        {{-- Stars Received --}}
        <section class="fb-card fb-mono-card mt-4">
            <div class="fb-card-header">
                <h2 class="fb-card-title">
                    <x-icon name="star" class="w-6 h-6 fb-primary-text" />
                    Stars Received ({{ $totalStars ?? 0 }})
                </h2>
            </div>
            <div class="fb-card-body">
                @if($starsReceived->isNotEmpty())
                    <div class="fb-stars-list">
                        @foreach($starsReceived as $star)
                            <div class="fb-star-item">
                                <img src="{{ $star->sender?->avatarUrl() }}" class="fb-avatar fb-avatar-sm" alt="">
                                <div class="flex-1">
                                    <a href="{{ route('social.profile', $star->sender?->username) }}" class="fb-text font-medium hover:underline">
                                        {{ $star->sender?->name }}
                                    </a>
                                    <div class="fb-text-muted text-xs">{{ $star->created_at->diffForHumans() }}</div>
                                </div>
                                <div class="flex items-center gap-1 text-yellow-500 font-bold">
                                    <x-icon name="star" class="w-4 h-4" />
                                    {{ $star->amount }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="fb-empty-state">
                        <x-icon name="star" class="w-12 h-12 mx-auto mb-3 opacity-30" />
                        <p class="fb-text-muted">No Stars received yet. Create engaging content!</p>
                    </div>
                @endif
            </div>
        </section>

        {{-- Withdraw --}}
        <section class="fb-card fb-mono-card mt-4">
            <div class="fb-card-header">
                <h2 class="fb-card-title">
                    <x-icon name="wallet" class="w-6 h-6 fb-primary-text" />
                    Withdraw Earnings
                </h2>
            </div>
            <div class="fb-card-body">
                @if($eligibility && $eligibility->is_eligible)
                    <form action="{{ route('social.monetization.withdraw') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="fb-label">Amount ($)</label>
                            <input type="number" name="amount" step="0.01" min="10"
                                   max="{{ $totalEarnings ?? 0 }}" class="fb-input" placeholder="50.00" required>
                        </div>
                        @if($withdrawalMethods->isNotEmpty())
                            <div>
                                <label class="fb-label">Withdrawal Method</label>
                                <select name="method_id" class="fb-input" required>
                                    <option value="">Select method...</option>
                                    @foreach($withdrawalMethods as $method)
                                        <option value="{{ $method->id }}">{{ $method->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <button type="submit" class="fb-btn fb-btn-primary w-full">
                            <x-icon name="wallet" class="w-5 h-5 inline" /> Request Withdrawal
                        </button>
                    </form>

                    @if($withdrawals->isNotEmpty())
                        <div class="fb-withdrawals-list mt-4">
                            <h4 class="fb-text font-semibold text-sm mb-2">Recent Withdrawals</h4>
                            @foreach($withdrawals as $withdrawal)
                                <div class="fb-withdrawal-item">
                                    <span class="fb-text font-medium">${{ number_format($withdrawal->amount, 2) }}</span>
                                    <span class="fb-text-muted text-xs">{{ $withdrawal->created_at->diffForHumans() }}</span>
                                    <span class="fb-withdrawal-status status-{{ $withdrawal->status ?? 'pending' }}">
                                        {{ ucfirst($withdrawal->status ?? 'pending') }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @else
                    <div class="fb-empty-state">
                        <x-icon name="wallet" class="w-12 h-12 mx-auto mb-3 opacity-30" />
                        <p class="fb-text-muted">You must be monetized to withdraw earnings.</p>
                    </div>
                @endif
            </div>
        </section>

    </div>
</div>

<style>
.fb-mono-card { max-width: 880px; margin: 0 auto; }
.fb-stats-grid {
    display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px; max-width: 880px; margin: 0 auto 20px;
}
.fb-stat-card {
    background: var(--fb-card); border-radius: 8px; padding: 20px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1); text-align: center;
}
.fb-stat-icon {
    width: 48px; height: 48px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 12px;
}
.fb-stat-blue { background: #dbeafe; color: #2563eb; }
.fb-stat-green { background: #dcfce7; color: #16a34a; }
.fb-stat-yellow { background: #fef3c7; color: #d97706; }
.fb-stat-purple { background: #f3e8ff; color: #9333ea; }
.fb-stat-value { font-size: 24px; font-weight: 800; color: var(--fb-text); }
.fb-stat-label { font-size: 13px; color: var(--fb-text-muted); margin-top: 4px; }

.fb-eligible-banner {
    display: flex; align-items: center; gap: 12px;
    background: #dcfce7; color: #166534; padding: 16px;
    border-radius: 8px; margin-bottom: 16px;
}
.fb-not-eligible-banner {
    display: flex; align-items: center; gap: 12px;
    background: #fef3c7; color: #92400e; padding: 16px;
    border-radius: 8px; margin-bottom: 16px;
}
.fb-mono-features { display: grid; gap: 12px; }
.fb-mono-feature {
    display: flex; align-items: center; gap: 12px;
    padding: 12px; border: 1px solid var(--fb-border); border-radius: 8px;
}
.fb-mono-feature.enabled { border-color: #16a34a; background: #f0fdf4; }
.fb-mono-status { margin-left: auto; font-weight: 600; font-size: 13px; padding: 4px 12px; border-radius: 12px; }
.fb-mono-feature.enabled .fb-mono-status { background: #16a34a; color: #fff; }
.fb-mono-feature:not(.enabled) .fb-mono-status { background: #9ca3af; color: #fff; }

.fb-requirements-list { display: grid; gap: 12px; }
.fb-requirement-item {
    display: flex; align-items: center; gap: 12px;
    padding: 12px; border: 1px solid var(--fb-border); border-radius: 8px;
}
.fb-requirement-item.met { border-color: #16a34a; }
.fb-requirement-item.unmet { border-color: #ef4444; }
.fb-req-progress {
    width: 120px; height: 8px; background: var(--fb-hover);
    border-radius: 4px; overflow: hidden;
}
.fb-req-progress-bar {
    height: 100%; background: var(--fb-primary); transition: width 0.3s;
}
.fb-requirement-item.met .fb-req-progress-bar { background: #16a34a; }

.fb-mono-grid {
    display: grid; grid-template-columns: 1fr 1fr;
    gap: 16px; max-width: 880px; margin: 16px auto;
}
@media (max-width: 768px) { .fb-mono-grid { grid-template-columns: 1fr; } }

.fb-earnings-list, .fb-subscribers-list, .fb-stars-list { display: grid; gap: 8px; }
.fb-earning-item, .fb-subscriber-item, .fb-star-item {
    display: flex; align-items: center; gap: 10px;
    padding: 8px; border-radius: 8px; transition: background 0.15s;
}
.fb-earning-item:hover, .fb-subscriber-item:hover, .fb-star-item:hover { background: var(--fb-hover); }
.fb-earning-info { flex: 1; }

.fb-withdrawals-list { display: grid; gap: 8px; }
.fb-withdrawal-item {
    display: flex; align-items: center; gap: 12px;
    padding: 8px 12px; border: 1px solid var(--fb-border); border-radius: 8px;
}
.fb-withdrawal-status { margin-left: auto; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; }
.status-pending { background: #fef3c7; color: #92400e; }
.status-approved { background: #dcfce7; color: #166534; }
.status-paid { background: #dbeafe; color: #1e40af; }
.status-rejected { background: #fee2e2; color: #991b1b; }

.fb-alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; max-width: 880px; margin-left: auto; margin-right: auto; }
.fb-alert-success { background: #dcfce7; color: #166534; }
.fb-alert-error { background: #fee2e2; color: #991b1b; }
.fb-alert-info { background: #dbeafe; color: #1e40af; }
</style>
@endsection
