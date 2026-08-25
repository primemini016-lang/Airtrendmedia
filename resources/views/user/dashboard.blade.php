@extends('layouts.user')
@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
{{-- Trial countdown banner --}}
@if(auth('web')->user()?->isOnTrial())
    @php $daysLeft = auth('web')->user()->trialDaysLeft(); @endphp
    <div class="card bg-gradient-to-r from-amber-50 to-orange-50 border-amber-300 mb-6">
        <div class="card-body flex items-center gap-4 flex-wrap">
            <div class="inline-flex w-12 h-12 rounded-xl bg-amber-500 items-center justify-center text-white flex-shrink-0">
                <x-icon name="clock" class="w-6 h-6" />
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="font-bold text-amber-800">Free Trial Active — {{ $daysLeft }} day(s) remaining</h3>
                <p class="text-sm text-amber-700">You're on a 3-day free trial. Pay the $5 activation fee before your trial ends to keep full access.</p>
            </div>
            <a href="{{ route('user.activate') }}" class="btn btn-primary text-sm flex-shrink-0">Activate Now</a>
        </div>
    </div>
@endif

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

<!-- Pending Tasks Section -->
@if($pendingTasks->isNotEmpty() || $pendingBookings->isNotEmpty() || $pendingProofs > 0 || $pendingWithdrawals > 0)
<div class="card mb-6 border-l-4 border-l-amber-400">
    <div class="card-body">
        <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
            <x-icon name="clock" class="w-5 h-5 text-amber-500" />
            Pending Items
        </h3>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Pending tasks (posted by user, awaiting admin approval) --}}
            <div class="border border-slate-100 rounded-lg p-4 {{ $pendingTasks->isNotEmpty() ? 'bg-amber-50' : '' }}">
                <p class="text-xs text-slate-500 mb-1">Tasks Awaiting Approval</p>
                <p class="text-2xl font-bold {{ $pendingTasks->isNotEmpty() ? 'text-amber-600' : 'text-slate-300' }}">{{ $pendingTasks->count() }}</p>
                @if($pendingTasks->isNotEmpty())
                <a href="{{ route('user.offers', ['status' => 'pending']) }}" class="text-xs text-blue-600 hover:underline mt-2 block">View pending tasks →</a>
                @endif
            </div>
            {{-- Pending bookings (submitted proof, awaiting employer review) --}}
            <div class="border border-slate-100 rounded-lg p-4 {{ $pendingBookings->isNotEmpty() ? 'bg-amber-50' : '' }}">
                <p class="text-xs text-slate-500 mb-1">Proofs Under Review</p>
                <p class="text-2xl font-bold {{ $pendingBookings->isNotEmpty() ? 'text-amber-600' : 'text-slate-300' }}">{{ $pendingBookings->count() }}</p>
                @if($pendingBookings->isNotEmpty())
                <a href="{{ route('user.bookings', ['status' => 'submitted']) }}" class="text-xs text-blue-600 hover:underline mt-2 block">View pending proofs →</a>
                @endif
            </div>
            {{-- Pending proofs (proofs submitted on user's tasks, awaiting review) --}}
            <div class="border border-slate-100 rounded-lg p-4 {{ $pendingProofs > 0 ? 'bg-amber-50' : '' }}">
                <p class="text-xs text-slate-500 mb-1">Proofs to Review</p>
                <p class="text-2xl font-bold {{ $pendingProofs > 0 ? 'text-amber-600' : 'text-slate-300' }}">{{ $pendingProofs }}</p>
                @if($pendingProofs > 0)
                <a href="{{ route('user.offers') }}" class="text-xs text-blue-600 hover:underline mt-2 block">Review proofs →</a>
                @endif
            </div>
            {{-- Pending withdrawals --}}
            <div class="border border-slate-100 rounded-lg p-4 {{ $pendingWithdrawals > 0 ? 'bg-amber-50' : '' }}">
                <p class="text-xs text-slate-500 mb-1">Withdrawals Pending</p>
                <p class="text-2xl font-bold {{ $pendingWithdrawals > 0 ? 'text-amber-600' : 'text-slate-300' }}">{{ $pendingWithdrawals }}</p>
                @if($pendingWithdrawals > 0)
                <a href="{{ route('user.transactions') }}" class="text-xs text-blue-600 hover:underline mt-2 block">View transactions →</a>
                @endif
            </div>
        </div>
        @if($pendingTasks->isNotEmpty())
        <div class="mt-4 border-t border-slate-100 pt-4">
            <p class="text-sm font-semibold text-slate-700 mb-2">Tasks awaiting admin approval:</p>
            <div class="space-y-2">
                @foreach($pendingTasks as $task)
                <div class="flex items-center justify-between gap-3 text-sm">
                    <span class="text-slate-600 truncate">{{ $task->title }}</span>
                    <span class="text-amber-600 font-semibold text-xs whitespace-nowrap">Pending</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endif

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
            <a href="{{ route('user.offers') }}" class="mt-4 block px-3 py-2 rounded-lg bg-amber-50 text-amber-700 text-sm font-semibold text-center">{{ $pendingProofs }} proof(s) awaiting your review <x-icon name="arrow-right" class="w-4 h-4 inline" /></a>
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
            <p class="text-slate-400 text-center py-8 text-sm">No active bookings. <a href="{{ route('user.tasks') }}" class="text-blue-600 hover:underline">Browse tasks <x-icon name="arrow-right" class="w-4 h-4 inline" /></a></p>
            @endif
        </div>
    </div>
</div>
@endsection
