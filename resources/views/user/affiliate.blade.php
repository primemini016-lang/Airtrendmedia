@extends('layouts.user')

@section('title', 'Affiliate Dashboard')
@section('heading', 'Affiliate Program')

@section('content')
@if(!$s->affiliate_enabled)
<div class="card bg-amber-50 border-amber-200 mb-6">
    <div class="card-body text-amber-800 text-sm">⚠ The affiliate program is currently disabled by the administrator. You can still share your link, but rewards will be credited once the program is re-enabled.</div>
</div>
@endif

<!-- Hero stats -->
<div class="card mb-6 auth-gradient text-white">
    <div class="card-body">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <p class="text-blue-100 text-sm">Earn <strong>{{ $currency->symbol }}{{ number_format($reward,2) }}</strong> ({{ number_format($reward,2) }} USD) for every friend who activates their account!</p>
                <p class="text-blue-100 text-xs mt-1">Your referral reward is paid automatically when a referred user pays their activation fee.</p>
            </div>
            <div class="text-right">
                <p class="text-blue-100 text-xs uppercase">Total Earnings</p>
                <p class="text-3xl font-bold">{{ number_format($totalEarnings,2) }} USD</p>
            </div>
        </div>
    </div>
</div>

<!-- Stat tiles -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="card">
        <div class="card-body">
            <p class="stat-label">Total Referrals</p>
            <p class="stat-value">{{ $total }}</p>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <p class="stat-label">Activated (Paid)</p>
            <p class="stat-value text-green-600">{{ $paidCount }}</p>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <p class="stat-label">Pending Activation</p>
            <p class="stat-value text-amber-600">{{ $pendingCount }}</p>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <p class="stat-label">Reward per Referral</p>
            <p class="stat-value text-blue-600">{{ number_format($reward,2) }} USD</p>
        </div>
    </div>
</div>

<!-- Share link -->
<div class="card mb-6">
    <div class="card-body">
        <h3 class="font-bold text-slate-800 mb-3">Your Referral Link</h3>
        <div class="flex gap-2 flex-wrap">
            <input type="text" id="refLink" readonly value="{{ route('register', ['ref' => $user->referral_code]) }}" class="input flex-1 bg-slate-50 font-mono text-sm">
            <button onclick="copyRef()" class="btn btn-primary">Copy Link</button>
            <button onclick="shareRef()" class="btn btn-outline">Share</button>
        </div>
        <p class="text-xs text-slate-400 mt-2">Referral code: <strong class="text-slate-600">{{ $user->referral_code }}</strong></p>
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-6">
    <!-- Monthly breakdown -->
    <div class="card">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 mb-3">Monthly Earnings</h3>
            @if($monthly->isEmpty())
                <p class="text-center text-slate-400 py-8 text-sm">No paid referrals yet.</p>
            @else
                <div class="space-y-3">
                    @foreach($monthly as $m)
                        <div class="flex items-center justify-between">
                            <span class="text-slate-600 text-sm">{{ $m->month }}</span>
                            <div class="flex items-center gap-3">
                                <span class="text-xs text-slate-400">{{ $m->count }} referrals</span>
                                <span class="font-bold text-blue-600">{{ number_format($m->total,2) }} USD</span>
                            </div>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2">
                            <div class="auth-gradient h-2 rounded-full" style="width: {{ min(100, ($m->count / max(1,$paidCount)) * 100) }}%"></div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Recent referrals -->
    <div class="card">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 mb-3">Recent Referrals</h3>
            @if($referrals->isEmpty())
                <p class="text-center text-slate-400 py-8 text-sm">You haven't referred anyone yet. Share your link to start earning!</p>
            @else
                <div class="space-y-2 max-h-80 overflow-y-auto">
                    @foreach($referrals as $ref)
                        @php
                            $statusMap = ['pending' => ['Pending','warning'], 'paid' => ['Paid','success'], 'revoked' => ['Revoked','danger']];
                            $st = $statusMap[$ref->status] ?? ['Unknown','muted'];
                        @endphp
                        <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50">
                            <div>
                                <p class="font-semibold text-slate-800 text-sm">@{{ $ref->referee->username }}</p>
                                <p class="text-xs text-slate-400">{{ $ref->created_at->format('M d, Y') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-sm {{ $ref->status === 'paid' ? 'text-green-600' : 'text-slate-500' }}">{{ $ref->status === 'paid' ? '+' . number_format($ref->reward_amount,2) : '—' }}</p>
                                <span class="badge badge-{{ $st[1] }}">{{ $st[0] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4">{{ $referrals->links() }}</div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    function copyRef() {
        const inp = document.getElementById('refLink');
        inp.select();
        inp.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(inp.value).then(() => {
            const btn = event.target;
            const orig = btn.textContent;
            btn.textContent = 'Copied!';
            setTimeout(() => btn.textContent = orig, 2000);
        });
    }
    function shareRef() {
        const url = document.getElementById('refLink').value;
        if (navigator.share) {
            navigator.share({ title: 'Join MiniWorkers', url });
        } else {
            copyRef();
        }
    }
</script>
@endpush
@endsection
