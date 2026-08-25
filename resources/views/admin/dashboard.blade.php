@extends('layouts.admin')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
<!-- Stat tiles -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="card">
        <div class="card-body">
            <p class="stat-label">Total Users</p>
            <p class="stat-value text-blue-600">{{ $stats['users'] }}</p>
            <p class="text-xs text-slate-400 mt-1">{{ $stats['active_users'] }} active</p>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <p class="stat-label">Total Tasks</p>
            <p class="stat-value text-slate-800">{{ $stats['tasks'] }}</p>
            <p class="text-xs text-slate-400 mt-1">{{ $stats['active_tasks'] }} live</p>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <p class="stat-label">Deposits Volume</p>
            <p class="stat-value text-green-600">{{ number_format($stats['deposits'],2) }}</p>
            <p class="text-xs text-slate-400 mt-1">USD total</p>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <p class="stat-label">Withdrawals Paid</p>
            <p class="stat-value text-amber-600">{{ number_format($stats['withdrawals'],2) }}</p>
            <p class="text-xs text-slate-400 mt-1">USD total</p>
        </div>
    </div>
</div>

<!-- Pending actions -->
<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <a href="{{ route('admin.deposits', ['status' => 'pending']) }}" class="card hover:border-blue-400 transition">
        <div class="card-body flex items-center justify-between">
            <div><p class="stat-label">Pending Deposits</p><p class="text-2xl font-bold text-amber-600">{{ $stats['pending_deposits'] }}</p></div>
            <span class="text-3xl">💰</span>
        </div>
    </a>
    <a href="{{ route('admin.withdrawals', ['status' => 'pending']) }}" class="card hover:border-blue-400 transition">
        <div class="card-body flex items-center justify-between">
            <div><p class="stat-label">Pending Withdrawals</p><p class="text-2xl font-bold text-amber-600">{{ $stats['pending_withdrawals'] }}</p></div>
            <span class="text-3xl">🏧</span>
        </div>
    </a>
    <a href="{{ route('admin.tasks', ['status' => 'pending']) }}" class="card hover:border-blue-400 transition">
        <div class="card-body flex items-center justify-between">
            <div><p class="stat-label">Pending Tasks</p><p class="text-2xl font-bold text-amber-600">{{ $stats['pending_tasks'] }}</p></div>
            <span class="text-3xl">📋</span>
        </div>
    </a>
    <a href="{{ route('admin.complaints', ['status' => 'open']) }}" class="card hover:border-blue-400 transition">
        <div class="card-body flex items-center justify-between">
            <div><p class="stat-label">Open Complaints</p><p class="text-2xl font-bold text-red-600">{{ $stats['complaints'] }}</p></div>
            <span class="text-3xl">⚠️</span>
        </div>
    </a>
</div>

<div class="grid lg:grid-cols-2 gap-6">
    <!-- Recent users -->
    <div class="card">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 mb-3">Recent Users</h3>
            <div class="space-y-2">
                @foreach($recentUsers as $u)
                    <a href="{{ route('admin.users.show', $u) }}" class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-50">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full auth-gradient flex items-center justify-center text-white text-xs font-bold">{{ strtoupper(substr($u->name,0,1)) }}</div>
                            <div>
                                <p class="text-sm font-semibold text-slate-800">{{ $u->name }}</p>
                                <p class="text-xs text-slate-400">{{ $u->email }}</p>
                            </div>
                        </div>
                        @if($u->is_active)<span class="badge badge-success">Active</span>@else<span class="badge badge-warning">Pending</span>@endif
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Recent transactions -->
    <div class="card">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 mb-3">Recent Transactions</h3>
            <div class="space-y-2">
                @foreach($recentTxns as $tx)
                    <div class="flex items-center justify-between p-2 rounded-lg">
                        <div>
                            <p class="text-sm font-semibold text-slate-800">@{{ $tx->user->username ?? '—' }}</p>
                            <p class="text-xs text-slate-400">{{ $tx->description }}</p>
                        </div>
                        <span class="font-bold text-sm {{ $tx->amount >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ $tx->amount >= 0 ? '+' : '' }}{{ number_format($tx->amount,2) }}</span>
                    </div>
                @endforeach
            </div>
            <a href="{{ route('admin.transactions') }}" class="block text-center text-blue-600 text-sm hover:underline mt-3">View all transactions →</a>
        </div>
    </div>
</div>
@endsection
