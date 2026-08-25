@extends('layouts.user')
@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
<!-- Stats -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="stat-tile">
        <p class="stat-label">Wallet Balance</p>
        <p class="stat-value text-blue-600">${{ number_format((float)$user->balance,2) }}</p>
    </div>
    <div class="stat-tile">
        <p class="stat-label">Total Earned</p>
        <p class="stat-value">${{ number_format((float)$user->total_earned,2) }}</p>
    </div>
    <div class="stat-tile">
        <p class="stat-label">Active Bookings</p>
        <p class="stat-value">{{ $openBookings->count() }}</p>
    </div>
    <div class="stat-tile">
        <p class="stat-label">My Active Tasks</p>
        <p class="stat-value">{{ $myActiveTasks->count() }}</p>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <!-- Quick actions -->
    <div class="card">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 mb-4">Quick Actions</h3>
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('user.tasks') }}" class="btn btn-primary text-xs">Find Tasks</a>
                <a href="{{ route('user.task.create') }}" class="btn btn-outline text-xs">Post a Task</a>
                <a href="{{ route('user.wallet') }}" class="btn btn-outline text-xs">Deposit</a>
                <a href="{{ route('user.withdraw') }}" class="btn btn-outline text-xs">Withdraw</a>
                <a href="{{ route('user.affiliate') }}" class="btn btn-outline text-xs">Affiliate</a>
                <a href="{{ route('user.profile') }}" class="btn btn-outline text-xs">Profile</a>
            </div>
            @if($pendingProofs > 0)
            <a href="{{ route('user.offers') }}" class="mt-4 block px-3 py-2 rounded-lg bg-amber-50 text-amber-700 text-sm font-semibold text-center">{{ $pendingProofs }} proof(s) awaiting your review →</a>
            @endif
            @if($pendingWithdrawals > 0)
            <p class="mt-2 text-center text-xs text-slate-400">{{ $pendingWithdrawals }} withdrawal(s) pending approval</p>
            @endif
        </div>
    </div>

    <!-- Recent transactions -->
    <div class="card lg:col-span-2">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-slate-800">Recent Transactions</h3>
                <a href="{{ route('user.transactions') }}" class="text-blue-600 text-sm hover:underline">View all</a>
            </div>
            @if($user->transactions->isNotEmpty())
            <table class="tbl">
                <thead><tr><th>Description</th><th>Type</th><th class="text-right">Amount</th><th>Date</th></tr></thead>
                <tbody>
                @foreach($user->transactions as $t)
                <tr>
                    <td class="text-slate-600">{{ $t->description ?? '—' }}</td>
                    <td><span class="badge badge-muted">{{ ucfirst(str_replace('_',' ',$t->type)) }}</span></td>
                    <td class="text-right font-semibold {{ $t->amount >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ $t->amount >= 0 ? '+' : '' }}${{ number_format(abs((float)$t->amount),2) }}</td>
                    <td class="text-slate-400 text-xs">{{ $t->created_at->format('M d, H:i') }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
            @else
            <p class="text-slate-400 text-center py-8 text-sm">No transactions yet. Complete tasks or deposit to get started.</p>
            @endif
        </div>
    </div>

    <!-- Open bookings -->
    <div class="card lg:col-span-3">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-slate-800">Your Active Bookings</h3>
                <a href="{{ route('user.bookings') }}" class="text-blue-600 text-sm hover:underline">View all</a>
            </div>
            @if($openBookings->isNotEmpty())
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($openBookings as $booking)
                <div class="border border-slate-100 rounded-lg p-4">
                    <p class="font-semibold text-slate-800 text-sm line-clamp-1">{{ $booking->task->title }}</p>
                    <p class="text-blue-600 font-bold mt-1">${{ number_format((float)$booking->task->price,2) }}</p>
                    <div class="flex items-center justify-between mt-3">
                        <span class="text-xs text-slate-400">Expires {{ $booking->expire_in?->format('M d, H:i') ?? '—' }}</span>
                        <a href="{{ route('user.task',$booking->task) }}" class="btn btn-primary text-xs">Open</a>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-slate-400 text-center py-8 text-sm">No active bookings. <a href="{{ route('user.tasks') }}" class="text-blue-600 hover:underline">Browse tasks →</a></p>
            @endif
        </div>
    </div>
</div>
@endsection
