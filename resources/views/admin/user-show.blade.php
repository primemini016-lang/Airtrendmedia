@extends('layouts.admin')

@section('title', 'User: ' . $user->username)
@section('heading', 'User Details')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.users') }}" class="text-blue-600 hover:underline text-sm"><x-icon name="arrow-left" class="w-4 h-4 inline" /> Back to Users</a>
</div>

<div class="grid lg:grid-cols-3 gap-6 mb-6">
    <!-- Profile card -->
    <div class="card">
        <div class="card-body text-center">
            @if($user->image)<img src="{{ asset('storage/'.$user->image) }}" class="w-24 h-24 rounded-full object-cover mx-auto mb-3 border-4 border-blue-100">@else<div class="w-24 h-24 rounded-full auth-gradient flex items-center justify-center text-white text-3xl font-bold mx-auto mb-3">{{ strtoupper(substr($user->name,0,1)) }}</div>@endif
            <h3 class="font-bold text-slate-800">{{ $user->name }}</h3>
            <p class="text-slate-400 text-sm">@{{ $user->username }}</p>
            <div class="mt-2">
                @if($user->banned)<span class="badge badge-danger">Banned</span>@elseif($user->is_active)<span class="badge badge-success">Active</span>@else<span class="badge badge-warning">Inactive</span>@endif
            </div>
            <div class="mt-4 pt-4 border-t border-slate-100 text-left space-y-1.5 text-sm">
                <div class="flex justify-between"><span class="text-slate-400">Email</span><span class="text-slate-700 truncate ml-2">{{ $user->email }}</span></div>
                <div class="flex justify-between"><span class="text-slate-400">Phone</span><span class="text-slate-700">{{ $user->phone ?? '—' }}</span></div>
                <div class="flex justify-between"><span class="text-slate-400">Country</span><span class="text-slate-700">{{ $user->country->name ?? '—' }}</span></div>
                <div class="flex justify-between"><span class="text-slate-400">Balance</span><span class="font-bold text-blue-600">{{ number_format((float)$user->balance,2) }} USD</span></div>
                <div class="flex justify-between"><span class="text-slate-400">Referral</span><span class="text-slate-700 font-mono">{{ $user->referral_code }}</span></div>
                <div class="flex justify-between"><span class="text-slate-400">Referred by</span><span class="text-slate-700">{{ $user->referrer->username ?? '—' }}</span></div>
                <div class="flex justify-between"><span class="text-slate-400">Joined</span><span class="text-slate-700">{{ $user->created_at->format('M d, Y') }}</span></div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="card lg:col-span-2">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 mb-4">Management Actions</h3>

            <div class="grid sm:grid-cols-2 gap-4 mb-6">
                <form action="{{ route('admin.users.ban', $user) }}" method="POST">
                    @csrf
                    <input type="hidden" name="banned" value="{{ $user->banned ? 0 : 1 }}">
                    <button class="btn {{ $user->banned ? 'btn-success' : 'btn-danger' }} w-full" onclick="return confirm('{{ $user->banned ? 'Unblock' : 'Block' }} this user?')">
                        {{ $user->banned ? '<x-icon name="check" class="w-4 h-4 inline" /> Unblock User' : '<x-icon name="x" class="w-4 h-4 inline" /> Block User' }}
                    </button>
                </form>
                <form action="{{ route('admin.users.activate', $user) }}" method="POST">
                    @csrf
                    <button class="btn btn-outline w-full" onclick="return confirm('Manually activate this user account?')" {{ $user->is_active ? 'disabled' : '' }}>
                        <x-icon name="check" class="w-4 h-4 inline" /> Activate Account
                    </button>
                </form>
            </div>

            <div class="border-t border-slate-100 pt-4">
                <h4 class="font-semibold text-slate-700 text-sm mb-3">Adjust Balance</h4>
                <form action="{{ route('admin.users.balance', $user) }}" method="POST" class="flex flex-wrap gap-2 items-end">
                    @csrf
                    <div class="flex-1 min-w-[120px]">
                        <label class="label">Amount (±USD)</label>
                        <input type="number" name="amount" class="input" placeholder="e.g. 5.00 or -5.00" step="0.01" required>
                    </div>
                    <div class="flex-1 min-w-[180px]">
                        <label class="label">Reason</label>
                        <input type="text" name="description" class="input" placeholder="Reason for adjustment" required>
                    </div>
                    <button class="btn btn-primary">Apply</button>
                </form>
                <p class="text-xs text-slate-400 mt-2">Positive amounts credit the user; negative amounts debit. All adjustments are logged in the transaction ledger.</p>
            </div>
        </div>
    </div>
</div>

<!-- Tabs: deposits, withdrawals, transactions, referrals -->
<div class="card mb-6">
    <div class="card-body">
        <h3 class="font-bold text-slate-800 mb-3">Deposits</h3>
        @if($user->deposits->isEmpty())<p class="text-slate-400 text-sm">No deposits.</p>
        @else
            <table class="tbl"><thead><tr><th>Date</th><th>Type</th><th>Amount</th><th>Status</th></tr></thead><tbody>
            @foreach($user->deposits as $d)
                @php $st = [0=>'Pending',1=>'Completed',2=>'Rejected'][$d->status] ?? 'Unknown'; @endphp
                <tr><td class="text-sm text-slate-500">{{ $d->date }}</td><td class="text-sm">{{ ucfirst($d->type) }}</td><td class="font-semibold">{{ number_format($d->amount,2) }}</td><td><span class="badge badge-{{ [0=>'warning',1=>'success',2=>'danger'][$d->status] ?? 'muted' }}">{{ $st }}</span></td></tr>
            @endforeach
            </tbody></table>
        @endif
    </div>
</div>

<div class="card mb-6">
    <div class="card-body">
        <h3 class="font-bold text-slate-800 mb-3">Withdrawals</h3>
        @if($user->withdrawals->isEmpty())<p class="text-slate-400 text-sm">No withdrawals.</p>
        @else
            <table class="tbl"><thead><tr><th>Date</th><th>Amount</th><th>Paid</th><th>Status</th></tr></thead><tbody>
            @foreach($user->withdrawals as $w)
                @php $st = [0=>'Pending',1=>'Paid',2=>'Rejected'][$w->status] ?? 'Unknown'; @endphp
                <tr><td class="text-sm text-slate-500">{{ $w->date }}</td><td class="font-semibold">{{ number_format($w->amount,2) }}</td><td class="text-blue-600">{{ number_format($w->paid,2) }}</td><td><span class="badge badge-{{ [0=>'warning',1=>'success',2=>'danger'][$w->status] ?? 'muted' }}">{{ $st }}</span></td></tr>
            @endforeach
            </tbody></table>
        @endif
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-6">
    <div class="card">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 mb-3">Recent Transactions</h3>
            @if($user->transactions->isEmpty())<p class="text-slate-400 text-sm">No transactions.</p>
            @else
                <div class="space-y-1.5 max-h-60 overflow-y-auto">
                @foreach($user->transactions as $tx)
                    <div class="flex justify-between text-sm"><span class="text-slate-600 truncate mr-2">{{ $tx->description }}</span><span class="font-semibold {{ $tx->amount >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ $tx->amount >= 0 ? '+' : '' }}{{ number_format($tx->amount,2) }}</span></div>
                @endforeach
                </div>
            @endif
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 mb-3">Affiliate Referrals</h3>
            @if($affiliateReferrals->isEmpty())<p class="text-slate-400 text-sm">No referrals made.</p>
            @else
                <div class="space-y-1.5">
                @foreach($affiliateReferrals as $ref)
                    <div class="flex justify-between text-sm"><span class="text-slate-600">@{{ $ref->referee->username }}</span><span class="badge badge-{{ $ref->status === 'paid' ? 'success' : ($ref->status === 'pending' ? 'warning' : 'danger') }}">{{ ucfirst($ref->status) }}</span></div>
                @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
