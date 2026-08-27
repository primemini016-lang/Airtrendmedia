@extends('layouts.admin')

@section('title', 'Affiliate Program')
@section('heading', 'Affiliate Program')

@section('content')
<!-- Stats -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="card"><div class="card-body"><p class="stat-label">Total Referrals</p><p class="stat-value">{{ $stats['total_referrals'] }}</p></div></div>
    <div class="card"><div class="card-body"><p class="stat-label">Paid Out</p><p class="stat-value text-green-600">{{ $stats['paid_referrals'] }}</p></div></div>
    <div class="card"><div class="card-body"><p class="stat-label">Pending</p><p class="stat-value text-amber-600">{{ $stats['pending_referrals'] }}</p></div></div>
    <div class="card"><div class="card-body"><p class="stat-label">Total Rewards Paid</p><p class="stat-value text-blue-600">{{ number_format($stats['total_paid_out'],2) }} USD</p></div></div>
</div>

<div class="grid lg:grid-cols-3 gap-6 mb-6">
    <!-- Top referrers -->
    <div class="card lg:col-span-1">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 mb-4"><x-icon name="award" class="w-4 h-4 inline" /> Top Referrers</h3>
            @if($topReferrers->isEmpty())<p class="text-slate-400 text-center py-8 text-sm">No paid referrals yet.</p>
            @else
                <div class="space-y-2">
                    @foreach($topReferrers as $i => $u)
                        <a href="{{ route('admin.users.show', $u) }}" class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-50">
                            <div class="flex items-center gap-2">
                                <span class="w-6 text-center font-bold {{ $i < 3 ? 'text-amber-500' : 'text-slate-400' }}">{{ $i + 1 }}</span>
                                <div><p class="text-sm font-semibold text-slate-800">{{ $u->name }}</p><p class="text-xs text-slate-400">{{ $u->paid_count }} paid referrals</p></div>
                            </div>
                            <span class="font-bold text-blue-600 text-sm">{{ number_format($u->paid_earnings ?? 0,2) }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- All referrals -->
    <div class="card lg:col-span-2">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-slate-800">All Referrals</h3>
                <div class="flex gap-2">
                    <a href="{{ route('admin.affiliate') }}" class="px-3 py-1 rounded text-xs font-semibold {{ !request('status') ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600' }}">All</a>
                    <a href="{{ route('admin.affiliate', ['status' => 'paid']) }}" class="px-3 py-1 rounded text-xs font-semibold {{ request('status') === 'paid' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600' }}">Paid</a>
                    <a href="{{ route('admin.affiliate', ['status' => 'pending']) }}" class="px-3 py-1 rounded text-xs font-semibold {{ request('status') === 'pending' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600' }}">Pending</a>
                    <a href="{{ route('admin.affiliate', ['status' => 'revoked']) }}" class="px-3 py-1 rounded text-xs font-semibold {{ request('status') === 'revoked' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600' }}">Revoked</a>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="tbl">
                    <thead><tr><th>Referrer</th><th>Referee</th><th>Reward</th><th>Status</th><th>Date</th><th></th></tr></thead>
                    <tbody>
                        @forelse($referrals as $ref)
                            @php $st = ['pending'=>['warning'],'paid'=>['success'],'revoked'=>['danger']][$ref->status] ?? ['muted']; @endphp
                            <tr>
                                <td class="text-sm"><a href="{{ route('admin.users.show', $ref->referrer) }}" class="hover:text-blue-600">{{ $ref->referrer->name }}</a></td>
                                <td class="text-sm"><a href="{{ route('admin.users.show', $ref->referee) }}" class="hover:text-blue-600">{{ $ref->referee->name }}</a></td>
                                <td class="font-semibold">{{ number_format($ref->reward_amount,2) }}</td>
                                <td><span class="badge badge-{{ $st[0] }}">{{ ucfirst($ref->status) }}</span></td>
                                <td class="text-sm text-slate-500">{{ $ref->created_at->format('M d, Y') }}</td>
                                <td>
                                    @if($ref->status === 'paid')
                                        <button onclick="document.getElementById('revoke-{{ $ref->id }}').classList.toggle('hidden')" class="text-red-600 text-xs hover:underline">Revoke</button>
                                        <form id="revoke-{{ $ref->id }}" action="{{ route('admin.affiliate.revoke', $ref) }}" method="POST" class="hidden mt-1">@csrf
                                            <input type="text" name="revoke_reason" class="input text-xs w-32 inline-block" placeholder="Reason" required>
                                            <button class="btn btn-danger text-xs" onclick="return confirm('Revoke this reward? It will be debited from the referrer. (Super admin only)')">Confirm</button>
                                        </form>
                                    @else<span class="text-slate-300 text-xs">—</span>@endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-slate-400 py-8">No referrals found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $referrals->links() }}</div>
        </div>
    </div>
</div>
@endsection
