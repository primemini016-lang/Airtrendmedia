@extends('layouts.admin')

@section('title', 'Anti-Cheat')
@section('heading', 'Anti-Cheat System')

@section('content')
<div class="space-y-6">

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card">
            <div class="card-body">
                <p class="stat-label">Total Flags</p>
                <p class="stat-value text-slate-700 dark:text-slate-200">{{ number_format($stats['total']) }}</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <p class="stat-label">Open Flags</p>
                <p class="stat-value text-amber-600">{{ number_format($stats['open']) }}</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <p class="stat-label">Resolved</p>
                <p class="stat-value text-green-600">{{ number_format($stats['resolved']) }}</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <p class="stat-label">High Severity</p>
                <p class="stat-value text-red-600">{{ number_format($stats['high']) }}</p>
            </div>
        </div>
    </div>

    {{-- Settings summary --}}
    <div class="card">
        <div class="card-body grid sm:grid-cols-2 gap-4 text-sm">
            <div class="flex items-center gap-2">
                <x-icon name="admin" class="w-4 h-4 text-blue-600" />
                <span>One account per IP: <strong class="{{ $enforceOnePerIp ? 'text-green-600' : 'text-red-600' }}">{{ $enforceOnePerIp ? 'Enforced' : 'Disabled' }}</strong></span>
            </div>
            <div class="flex items-center gap-2">
                <x-icon name="notifications" class="w-4 h-4 text-blue-600" />
                <span>Action threshold: <strong>{{ number_format($threshold) }}</strong> actions per window</span>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.anti-cheat') }}" class="badge {{ !request('status') ? 'badge-success' : 'badge-default' }}">All Status</a>
        <a href="{{ route('admin.anti-cheat', ['status'=>'open']) }}" class="badge {{ request('status')==='open' ? 'badge-warning' : 'badge-default' }}">Open</a>
        <a href="{{ route('admin.anti-cheat', ['status'=>'resolved']) }}" class="badge {{ request('status')==='resolved' ? 'badge-success' : 'badge-default' }}">Resolved</a>
        <a href="{{ route('admin.anti-cheat', ['status'=>'dismissed']) }}" class="badge {{ request('status')==='dismissed' ? 'badge-default' : 'badge-default' }}">Dismissed</a>
    </div>

    {{-- Flags table --}}
    <div class="card">
        <div class="card-body">
            @if($flags->isEmpty())
                <p class="text-center text-slate-400 py-8">No anti-cheat flags. All clear!</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-slate-400 border-b border-slate-200 dark:border-slate-700">
                                <th class="pb-2 pr-4">User</th>
                                <th class="pb-2 pr-4">Type</th>
                                <th class="pb-2 pr-4">Description</th>
                                <th class="pb-2 pr-4">IP</th>
                                <th class="pb-2 pr-4">Severity</th>
                                <th class="pb-2 pr-4">Status</th>
                                <th class="pb-2 pr-4">Date</th>
                                <th class="pb-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($flags as $flag)
                                <tr class="border-b border-slate-100 dark:border-slate-800">
                                    <td class="py-3 pr-4 text-slate-700 dark:text-slate-200">{{ $flag->user->name ?? '—' }}</td>
                                    <td class="py-3 pr-4 text-slate-500 capitalize">{{ str_replace('_',' ',$flag->type) }}</td>
                                    <td class="py-3 pr-4 text-slate-500 max-w-xs truncate">{{ $flag->description }}</td>
                                    <td class="py-3 pr-4 text-slate-400 font-mono text-xs">{{ $flag->ip_address ?? '—' }}</td>
                                    <td class="py-3 pr-4">
                                        @if($flag->severity === 'high')<span class="badge badge-danger">High</span>
                                        @elseif($flag->severity === 'medium')<span class="badge badge-warning">Medium</span>
                                        @else<span class="badge badge-default">Low</span>@endif
                                    </td>
                                    <td class="py-3 pr-4">
                                        @if($flag->status === 'open')<span class="badge badge-warning">Open</span>
                                        @elseif($flag->status === 'resolved')<span class="badge badge-success">Resolved</span>
                                        @else<span class="badge badge-default">Dismissed</span>@endif
                                    </td>
                                    <td class="py-3 pr-4 text-xs text-slate-400">{{ $flag->created_at->format('M d, Y') }}</td>
                                    <td class="py-3">
                                        @if($flag->status === 'open')
                                            <div class="flex gap-2">
                                                <form action="{{ route('admin.anti-cheat.resolve', $flag) }}" method="POST">@csrf<button class="text-green-600 text-xs hover:underline">Resolve</button></form>
                                                <form action="{{ route('admin.anti-cheat.dismiss', $flag) }}" method="POST">@csrf<button class="text-slate-400 text-xs hover:underline">Dismiss</button></form>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $flags->links() }}
            @endif
        </div>
    </div>
</div>
@endsection
