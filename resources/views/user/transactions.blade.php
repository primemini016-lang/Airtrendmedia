@extends('layouts.user')

@section('title', 'Transactions')
@section('heading', 'Transaction History')

@section('content')
<div class="mb-5">
    <p class="text-slate-500 text-sm">A complete, tamper-proof ledger of every balance movement on your account.</p>
</div>

<div class="flex flex-wrap gap-2 mb-5">
    <a href="{{ route('user.transactions') }}" class="px-4 py-2 rounded-lg text-sm font-semibold {{ !request('type') ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">All</a>
    @php
        $types = ['deposit' => 'Deposits', 'withdrawal' => 'Withdrawals', 'task_creation' => 'Task Creation', 'task_reward' => 'Task Rewards', 'activation' => 'Activation', 'affiliate' => 'Affiliate', 'admin_adjustment' => 'Adjustments'];
    @endphp
    @foreach($types as $key => $label)
        <a href="{{ route('user.transactions', ['type' => $key]) }}" class="px-4 py-2 rounded-lg text-sm font-semibold {{ request('type') === $key ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">{{ $label }}</a>
    @endforeach
</div>

@if($transactions->isEmpty())
    <div class="card text-center py-16">
        <div class="text-5xl mb-3"><x-icon name="transactions" class="w-4 h-4 inline" /></div>
        <h3 class="text-lg font-bold text-slate-800">No transactions yet</h3>
        <p class="text-slate-500 mt-1">Your financial activity will appear here.</p>
    </div>
@else
    <div class="card">
        <div class="overflow-x-auto">
            <table class="tbl">
                <thead>
                    <tr><th>Date</th><th>Type</th><th>Description</th><th>Reference</th><th class="text-right">Amount</th></tr>
                </thead>
                <tbody>
                    @foreach($transactions as $tx)
                        <tr>
                            <td class="text-slate-500 text-sm whitespace-nowrap">{{ $tx->created_at->format('M d, Y H:i') }}</td>
                            <td><span class="badge badge-muted">{{ ucfirst(str_replace('_',' ',$tx->type)) }}</span></td>
                            <td class="text-slate-700 text-sm">{{ $tx->description }}</td>
                            <td class="text-xs text-slate-400">{{ $tx->reference }}</td>
                            <td class="text-right font-bold {{ $tx->amount >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $tx->amount >= 0 ? '+' : '' }}{{ money((float)$tx->amount) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $transactions->links() }}</div>
    </div>
@endif
@endsection
