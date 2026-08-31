@extends('layouts.admin')

@section('title', 'Transactions')
@section('heading', 'Transaction Ledger')

@section('content')
<!-- Summary -->
<div class="grid sm:grid-cols-3 gap-4 mb-6">
    <div class="card"><div class="card-body"><p class="stat-label">Total Inflow</p><p class="stat-value text-green-600">{{ money((float)$summary['total_in']) }}</p></div></div>
    <div class="card"><div class="card-body"><p class="stat-label">Total Outflow</p><p class="stat-value text-red-600">{{ money((float)$summary['total_out']) }}</p></div></div>
    <div class="card"><div class="card-body"><p class="stat-label">Total Transactions</p><p class="stat-value text-slate-800">{{ $summary['count'] }}</p></div></div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]"><label class="label">Search (user, reference, description)</label><input type="text" name="q" class="input" value="{{ request('q') }}"></div>
            <div class="min-w-[150px]"><label class="label">Type</label>
                <select name="type" class="input"><option value="">All</option>
                    @php $types = ['deposit','withdrawal','task_creation','task_credit','task_refund','activation','affiliate_reward','affiliate_revoke','admin_adjustment']; @endphp
                    @foreach($types as $t)<option value="{{ $t }}" {{ request('type') === $t ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$t)) }}</option>@endforeach
                </select>
            </div>
            <button class="btn btn-primary">Filter</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="overflow-x-auto">
        <table class="tbl">
            <thead><tr><th>Date</th><th>User</th><th>Type</th><th>Description</th><th>Reference</th><th class="text-right">Amount</th></tr></thead>
            <tbody>
                @forelse($transactions as $tx)
                    <tr>
                        <td class="text-slate-500 text-sm whitespace-nowrap">{{ $tx->created_at->format('M d, Y H:i') }}</td>
                        <td><a href="{{ route('admin.users.show', $tx->user) }}" class="text-slate-700 hover:text-blue-600">{{ $tx->user->username ?? '—' }}</a></td>
                        <td><span class="badge badge-muted">{{ ucfirst(str_replace('_',' ',$tx->type)) }}</span></td>
                        <td class="text-slate-600 text-sm max-w-[260px] truncate" title="{{ $tx->description }}">{{ $tx->description }}</td>
                        <td class="text-xs text-slate-400">{{ $tx->reference }}</td>
                        <td class="text-right font-bold {{ $tx->amount >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ $tx->amount >= 0 ? '+' : '' }}{{ money((float)$tx->amount) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-slate-400 py-8">No transactions found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $transactions->links() }}</div>
</div>
@endsection
