@extends('layouts.user')

@section('title', 'Withdraw')
@section('heading', 'Withdraw Funds')

@section('content')
<div class="grid sm:grid-cols-2 gap-4 mb-6">
    <div class="card">
        <div class="card-body">
            <p class="stat-label">Available Balance</p>
            <p class="stat-value text-blue-600">{{ number_format((float)auth()->user()->balance,2) }} USD</p>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <p class="stat-label">Withdrawal Fee</p>
            <p class="stat-value text-slate-800 text-lg">{{ $s->withdraw_com }}%</p>
            <p class="text-xs text-slate-400">Deducted from withdrawal amount</p>
        </div>
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-6">
    <!-- Withdrawal form -->
    <div class="card">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 mb-1">Request a Withdrawal</h3>
            <p class="text-sm text-slate-500 mb-4">Choose a payout method and enter your account details.</p>
            <form action="{{ route('user.withdraw.request') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="label">Payout Method <span class="text-red-500">*</span></label>
                    <select name="method_id" class="input" required>
                        <option value="">Select method…</option>
                        @foreach($methods as $m)
                            <option value="{{ $m->id }}">{{ $m->name }} @if($m->min_amount > 0)(min {{ number_format($m->min_amount,2) }} USD)@endif</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label class="label">Amount (USD) <span class="text-red-500">*</span></label>
                    <input type="number" name="amount" class="input" placeholder="10.00" min="1" step="0.01" required>
                </div>
                <div class="mb-4">
                    <label class="label">Account / Payout Details <span class="text-red-500">*</span></label>
                    <textarea name="details" class="input" rows="4" required maxlength="2000" placeholder="Enter your account number, wallet address, or any details needed for this payout method."></textarea>
                </div>
                <button type="submit" class="btn btn-primary w-full" onclick="return confirm('Confirm this withdrawal request? The amount will be held until approved.')">Submit Withdrawal Request</button>
            </form>
        </div>
    </div>

    <!-- Withdrawal history -->
    <div class="card">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 mb-3">Withdrawal History</h3>
            @if($withdrawals->isEmpty())
                <p class="text-center text-slate-400 py-8">No withdrawals yet.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="tbl">
                        <thead>
                            <tr><th>Date</th><th>Amount</th><th>Fee</th><th>You Get</th><th>Method</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            @foreach($withdrawals as $w)
                                @php
                                    $statusMap = [0 => ['Pending','warning'], 1 => ['Paid','success'], 2 => ['Rejected','danger']];
                                    $st = $statusMap[$w->status] ?? ['Unknown','muted'];
                                @endphp
                                <tr>
                                    <td class="text-slate-600 text-sm">{{ $w->date }}</td>
                                    <td class="font-semibold">{{ number_format($w->amount,2) }}</td>
                                    <td class="text-slate-500">{{ number_format($w->fee,2) }}</td>
                                    <td class="font-semibold text-blue-600">{{ number_format($w->paid,2) }}</td>
                                    <td class="text-sm text-slate-600">{{ $w->method->name ?? '—' }}</td>
                                    <td><span class="badge badge-{{ $st[1] }}">{{ $st[0] }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $withdrawals->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
