@extends('layouts.admin')

@section('title', 'Verification Badges')
@section('heading', 'Blue Verification Management')

@section('content')
<div class="space-y-6">

    {{-- Settings summary --}}
    <div class="grid sm:grid-cols-3 gap-4">
        <div class="card">
            <div class="card-body">
                <p class="stat-label">Monthly Fee</p>
                <p class="stat-value text-blue-600">{{ money((float)$monthlyFee) }}</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <p class="stat-label">Auto-Approval</p>
                <p class="stat-value @if($autoApprove) text-green-600 @else text-slate-500 @endif">{{ $autoApprove ? 'Enabled' : 'Disabled' }}</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.verification.settings') }}" method="POST" class="flex items-center justify-between">
                    @csrf
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">Toggle Auto-Approve</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="enabled" value="1" class="sr-only peer" @if($autoApprove) checked @endif onchange="this.form.submit()">
                        <div class="w-12 h-6 bg-slate-300 peer-checked:bg-blue-600 rounded-full peer transition"></div>
                        <span class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full transition peer-checked:translate-x-6"></span>
                    </label>
                </form>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.verification') }}" class="badge {{ !request('status') ? 'badge-success' : 'badge-default' }}">All</a>
        <a href="{{ route('admin.verification', ['status'=>'pending']) }}" class="badge {{ request('status')==='pending' ? 'badge-warning' : 'badge-default' }}">Pending</a>
        <a href="{{ route('admin.verification', ['status'=>'verified']) }}" class="badge {{ request('status')==='verified' ? 'badge-success' : 'badge-default' }}">Verified</a>
        <a href="{{ route('admin.verification', ['status'=>'rejected']) }}" class="badge {{ request('status')==='rejected' ? 'badge-danger' : 'badge-default' }}">Rejected</a>
        <a href="{{ route('admin.verification', ['status'=>'revoked']) }}" class="badge {{ request('status')==='revoked' ? 'badge-danger' : 'badge-default' }}">Revoked</a>
    </div>

    {{-- Badges table --}}
    <div class="card">
        <div class="card-body">
            @if($badges->isEmpty())
                <p class="text-center text-slate-400 py-8">No verification requests found.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-slate-400 border-b border-slate-200 dark:border-slate-700">
                                <th class="pb-2 pr-4">User</th>
                                <th class="pb-2 pr-4">Full Name</th>
                                <th class="pb-2 pr-4">Category</th>
                                <th class="pb-2 pr-4">Valid Until</th>
                                <th class="pb-2 pr-4">Status</th>
                                <th class="pb-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($badges as $badge)
                                <tr class="border-b border-slate-100 dark:border-slate-800">
                                    <td class="py-3 pr-4">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-full auth-gradient flex items-center justify-center text-white text-xs font-bold">{{ strtoupper(substr($badge->user->name ?? 'U',0,1)) }}</div>
                                            <span class="text-slate-700 dark:text-slate-200">{{ $badge->user->name ?? '—' }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 pr-4 text-slate-600 dark:text-slate-300">{{ $badge->full_name }}</td>
                                    <td class="py-3 pr-4 text-slate-600 dark:text-slate-300 capitalize">{{ str_replace('_',' ',$badge->category) }}</td>
                                    <td class="py-3 pr-4 text-slate-400 text-xs">{{ $badge->valid_until?->format('M d, Y') ?? '—' }}</td>
                                    <td class="py-3 pr-4">
                                        @if($badge->status === 'verified')<span class="badge" style="background:#0057FF;color:white">Verified</span>
                                        @elseif($badge->status === 'rejected')<span class="badge badge-danger">Rejected</span>
                                        @elseif($badge->status === 'revoked')<span class="badge badge-danger">Revoked</span>
                                        @else<span class="badge badge-warning">Pending</span>@endif
                                    </td>
                                    <td class="py-3">
                                        <a href="{{ route('admin.verification.show', $badge) }}" class="text-blue-600 hover:underline text-sm">Review →</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $badges->links() }}
            @endif
        </div>
    </div>
</div>
@endsection
