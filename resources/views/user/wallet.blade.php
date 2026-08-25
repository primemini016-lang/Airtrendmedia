@extends('layouts.user')

@section('title', 'Wallet')
@section('heading', 'My Wallet')

@section('content')
<div class="grid sm:grid-cols-3 gap-4 mb-6">
    <div class="card">
        <div class="card-body">
            <p class="stat-label">Available Balance</p>
            <p class="stat-value text-blue-600">{{ number_format((float)auth()->user()->balance,2) }} USD</p>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <p class="stat-label">Display Currency</p>
            <p class="stat-value text-slate-800 text-lg">{{ $currency->code }} ({{ $currency->symbol }})</p>
            <p class="text-xs text-slate-400">1 USD = {{ number_format($currency->usd_value,2) }} {{ $currency->code }}</p>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <p class="stat-label">Payment Gateway</p>
            <p class="stat-value text-green-600 text-lg">Paystack</p>
            <p class="text-xs text-slate-400">Secure online deposits</p>
        </div>
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-6 mb-6">
    <!-- Deposit form -->
    <div class="card">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 mb-1">Deposit Funds</h3>
            <p class="text-sm text-slate-500 mb-4">Enter the amount in USD. You will be charged in {{ $currency->code }} via Paystack.</p>
            <form action="{{ route('user.wallet.deposit') }}" method="POST">
                @csrf
                <label class="label">Amount (USD)</label>
                <div class="flex gap-2">
                    <input type="number" name="amount" class="input" placeholder="10.00" min="1" max="10000" step="0.01" required>
                    <button type="submit" class="btn btn-primary whitespace-nowrap">Pay via Paystack</button>
                </div>
                <p class="text-xs text-slate-400 mt-2">You'll be redirected to Paystack's secure checkout to complete the payment.</p>
            </form>
        </div>
    </div>

    <!-- Quick links -->
    <div class="card">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 mb-3">Quick Actions</h3>
            <div class="space-y-2">
                <a href="{{ route('user.withdraw') }}" class="flex items-center justify-between p-3 rounded-lg bg-slate-50 hover:bg-blue-50 transition">
                    <span class="font-semibold text-slate-700"><x-icon name="wallet" class="w-4 h-4 inline" /> Withdraw Funds</span>
                    <span class="text-blue-600"><x-icon name="arrow-right" class="w-4 h-4 inline" /></span>
                </a>
                <a href="{{ route('user.transactions') }}" class="flex items-center justify-between p-3 rounded-lg bg-slate-50 hover:bg-blue-50 transition">
                    <span class="font-semibold text-slate-700"><x-icon name="transactions" class="w-4 h-4 inline" /> View Transactions</span>
                    <span class="text-blue-600"><x-icon name="arrow-right" class="w-4 h-4 inline" /></span>
                </a>
                <a href="{{ route('user.task.create') }}" class="flex items-center justify-between p-3 rounded-lg bg-slate-50 hover:bg-blue-50 transition">
                    <span class="font-semibold text-slate-700"><x-icon name="ads" class="w-4 h-4 inline" /> Create a Task</span>
                    <span class="text-blue-600"><x-icon name="arrow-right" class="w-4 h-4 inline" /></span>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Deposit history -->
<div class="card">
    <div class="card-body">
        <h3 class="font-bold text-slate-800 mb-3">Deposit History</h3>
        @if($deposits->isEmpty())
            <p class="text-center text-slate-400 py-8">No deposits yet.</p>
        @else
            <div class="overflow-x-auto">
                <table class="tbl">
                    <thead>
                        <tr><th>Date</th><th>Amount (USD)</th><th>Paid</th><th>Reference</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @foreach($deposits as $dep)
                            @php
                                $statusMap = [0 => ['Pending','warning'], 1 => ['Completed','success'], 2 => ['Rejected','danger']];
                                $st = $statusMap[$dep->status] ?? ['Unknown','muted'];
                            @endphp
                            <tr>
                                <td class="text-slate-600">{{ $dep->date }}</td>
                                <td class="font-semibold">{{ number_format($dep->amount,2) }}</td>
                                <td class="text-slate-600">{{ number_format($dep->amount_paid,2) }}</td>
                                <td class="text-xs text-slate-400">{{ $dep->reference }}</td>
                                <td><span class="badge badge-{{ $st[1] }}">{{ $st[0] }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $deposits->links() }}</div>
        @endif
    </div>
</div>
@endsection
