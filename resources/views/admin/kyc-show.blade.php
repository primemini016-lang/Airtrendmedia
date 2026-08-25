@extends('layouts.admin')

@section('title', 'KYC Review')
@section('heading', 'Review KYC Submission')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center gap-3">
        <a href="{{ route('admin.kyc') }}" class="btn btn-ghost text-sm">← Back to KYC</a>
    </div>

    {{-- User info --}}
    <div class="card">
        <div class="card-body">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-full auth-gradient flex items-center justify-center text-white text-xl font-bold">{{ strtoupper(substr($kyc->user->name ?? 'U',0,1)) }}</div>
                <div class="flex-1">
                    <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ $kyc->user->name }}</h2>
                    <p class="text-sm text-slate-500">{{ $kyc->user->email }}</p>
                </div>
                @if($kyc->status === 'approved')<span class="badge badge-success">Approved</span>
                @elseif($kyc->status === 'rejected')<span class="badge badge-danger">Rejected</span>
                @else<span class="badge badge-warning">Pending</span>@endif
            </div>
        </div>
    </div>

    {{-- Details --}}
    <div class="card">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 dark:text-slate-100 mb-3">Submission Details</h3>
            <div class="grid sm:grid-cols-2 gap-4 text-sm">
                <div><span class="text-slate-400">Full Name:</span> <span class="font-semibold">{{ $kyc->full_name }}</span></div>
                <div><span class="text-slate-400">ID Type:</span> <span class="font-semibold capitalize">{{ str_replace('_',' ',$kyc->id_type) }}</span></div>
                <div><span class="text-slate-400">ID Number:</span> <span class="font-semibold">{{ $kyc->id_number }}</span></div>
                <div><span class="text-slate-400">Date of Birth:</span> <span class="font-semibold">{{ $kyc->date_of_birth?->format('M d, Y') }}</span></div>
                <div><span class="text-slate-400">Country:</span> <span class="font-semibold">{{ $kyc->country }}</span></div>
                <div><span class="text-slate-400">Address:</span> <span class="font-semibold">{{ $kyc->address }}</span></div>
                <div><span class="text-slate-400">Submitted:</span> <span class="font-semibold">{{ $kyc->created_at->format('M d, Y H:i') }}</span></div>
                <div><span class="text-slate-400">IP Address:</span> <span class="font-semibold">{{ $kyc->user->registration_ip ?? 'N/A' }}</span></div>
            </div>
        </div>
    </div>

    {{-- Documents --}}
    <div class="card">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 dark:text-slate-100 mb-3">Uploaded Documents</h3>
            <div class="grid sm:grid-cols-3 gap-4">
                @if($kyc->document_front)
                    <div>
                        <p class="text-xs text-slate-400 mb-1">Document Front</p>
                        <a href="{{ Storage::url($kyc->document_front) }}" target="_blank">
                            <img src="{{ Storage::url($kyc->document_front) }}" class="w-full h-48 object-cover rounded-lg border border-slate-200 dark:border-slate-700">
                        </a>
                    </div>
                @endif
                @if($kyc->document_back)
                    <div>
                        <p class="text-xs text-slate-400 mb-1">Document Back</p>
                        <a href="{{ Storage::url($kyc->document_back) }}" target="_blank">
                            <img src="{{ Storage::url($kyc->document_back) }}" class="w-full h-48 object-cover rounded-lg border border-slate-200 dark:border-slate-700">
                        </a>
                    </div>
                @endif
                @if($kyc->selfie)
                    <div>
                        <p class="text-xs text-slate-400 mb-1">Selfie</p>
                        <a href="{{ Storage::url($kyc->selfie) }}" target="_blank">
                            <img src="{{ Storage::url($kyc->selfie) }}" class="w-full h-48 object-cover rounded-lg border border-slate-200 dark:border-slate-700">
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if($kyc->status === 'pending')
        {{-- Approve / Reject actions --}}
        <div class="card">
            <div class="card-body">
                <h3 class="font-bold text-slate-800 dark:text-slate-100 mb-3">Review Actions</h3>
                <div class="grid sm:grid-cols-2 gap-4">
                    <form action="{{ route('admin.kyc.approve', $kyc) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success w-full">✓ Approve KYC</button>
                    </form>
                    <form action="{{ route('admin.kyc.reject', $kyc) }}" method="POST">
                        @csrf
                        <label class="form-label">Rejection Reason</label>
                        <div class="flex gap-2">
                            <input type="text" name="reason" required class="form-input" placeholder="Reason for rejection">
                            <button type="submit" class="btn btn-danger flex-shrink-0">Reject</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
