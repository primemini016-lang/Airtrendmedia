@extends('layouts.admin')

@section('title', 'KYC Management')
@section('heading', 'KYC Verification Management')

@section('content')
<div class="space-y-6">

    {{-- Auto-approval toggle --}}
    <div class="card">
        <div class="card-body flex items-center justify-between">
            <div>
                <h3 class="font-bold text-slate-800 dark:text-slate-100">Auto-Approval</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">When enabled, KYC submissions are automatically approved upon submission.</p>
            </div>
            <form action="{{ route('admin.kyc.auto-approval') }}" method="POST">
                @csrf
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="enabled" value="1" class="sr-only peer" @if($autoApprove) checked @endif onchange="this.form.submit()">
                    <div class="w-12 h-6 bg-slate-300 peer-checked:bg-blue-600 rounded-full peer transition"></div>
                    <span class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full transition peer-checked:translate-x-6"></span>
                </label>
            </form>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.kyc') }}" class="badge {{ !request('status') ? 'badge-success' : 'badge-default' }}">All</a>
        <a href="{{ route('admin.kyc', ['status'=>'pending']) }}" class="badge {{ request('status')==='pending' ? 'badge-warning' : 'badge-default' }}">Pending</a>
        <a href="{{ route('admin.kyc', ['status'=>'approved']) }}" class="badge {{ request('status')==='approved' ? 'badge-success' : 'badge-default' }}">Approved</a>
        <a href="{{ route('admin.kyc', ['status'=>'rejected']) }}" class="badge {{ request('status')==='rejected' ? 'badge-danger' : 'badge-default' }}">Rejected</a>
    </div>

    {{-- Submissions table --}}
    <div class="card">
        <div class="card-body">
            @if($submissions->isEmpty())
                <p class="text-center text-slate-400 py-8">No KYC submissions found.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-slate-400 border-b border-slate-200 dark:border-slate-700">
                                <th class="pb-2 pr-4">User</th>
                                <th class="pb-2 pr-4">Name</th>
                                <th class="pb-2 pr-4">ID Type</th>
                                <th class="pb-2 pr-4">Country</th>
                                <th class="pb-2 pr-4">Submitted</th>
                                <th class="pb-2 pr-4">Status</th>
                                <th class="pb-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($submissions as $kyc)
                                <tr class="border-b border-slate-100 dark:border-slate-800">
                                    <td class="py-3 pr-4">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-full auth-gradient flex items-center justify-center text-white text-xs font-bold">{{ strtoupper(substr($kyc->user->name ?? 'U',0,1)) }}</div>
                                            <span class="text-slate-700 dark:text-slate-200">{{ $kyc->user->name ?? '—' }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 pr-4 text-slate-600 dark:text-slate-300">{{ $kyc->full_name }}</td>
                                    <td class="py-3 pr-4 text-slate-600 dark:text-slate-300 capitalize">{{ str_replace('_',' ',$kyc->id_type) }}</td>
                                    <td class="py-3 pr-4 text-slate-600 dark:text-slate-300">{{ $kyc->country }}</td>
                                    <td class="py-3 pr-4 text-slate-400 text-xs">{{ $kyc->created_at->format('M d, Y') }}</td>
                                    <td class="py-3 pr-4">
                                        @if($kyc->status === 'approved')<span class="badge badge-success">Approved</span>
                                        @elseif($kyc->status === 'rejected')<span class="badge badge-danger">Rejected</span>
                                        @else<span class="badge badge-warning">Pending</span>@endif
                                    </td>
                                    <td class="py-3">
                                        <a href="{{ route('admin.kyc.show', $kyc) }}" class="text-blue-600 hover:underline text-sm">Review →</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $submissions->links() }}
            @endif
        </div>
    </div>
</div>
@endsection
