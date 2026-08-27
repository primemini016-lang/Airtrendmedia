@extends('layouts.admin')

@section('title', 'Verification Review')
@section('heading', 'Review Verification Request')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center gap-3">
        <a href="{{ route('admin.verification') }}" class="btn btn-ghost text-sm">← Back to Verifications</a>
    </div>

    {{-- User info --}}
    <div class="card">
        <div class="card-body">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-full auth-gradient flex items-center justify-center text-white text-xl font-bold relative">
                    {{ strtoupper(substr($badge->user->name ?? 'U',0,1)) }}
                    @if($badge->status === 'verified')
                        <span class="absolute -bottom-1 -right-1 w-6 h-6 bg-blue-600 rounded-full flex items-center justify-center border-2 border-white">
                            <svg width="14" height="14" fill="white" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                        </span>
                    @endif
                </div>
                <div class="flex-1">
                    <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ $badge->user->name }}</h2>
                    <p class="text-sm text-slate-500">{{ $badge->user->email }}</p>
                </div>
                @if($badge->status === 'verified')<span class="badge" style="background:#1877F2;color:white">Verified</span>
                @elseif($badge->status === 'rejected')<span class="badge badge-danger">Rejected</span>
                @elseif($badge->status === 'revoked')<span class="badge badge-danger">Revoked</span>
                @else<span class="badge badge-warning">Pending</span>@endif
            </div>
        </div>
    </div>

    {{-- Details --}}
    <div class="card">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 dark:text-slate-100 mb-3">Verification Details</h3>
            <div class="grid sm:grid-cols-2 gap-4 text-sm">
                <div><span class="text-slate-400">Full Name:</span> <span class="font-semibold">{{ $badge->full_name }}</span></div>
                <div><span class="text-slate-400">Category:</span> <span class="font-semibold capitalize">{{ str_replace('_',' ',$badge->category) }}</span></div>
                <div><span class="text-slate-400">Monthly Fee:</span> <span class="font-semibold">${{ number_format($badge->monthly_fee, 2) }}</span></div>
                <div><span class="text-slate-400">Amount Paid:</span> <span class="font-semibold">${{ number_format($badge->amount_paid ?? 0, 2) }}</span></div>
                <div><span class="text-slate-400">Valid From:</span> <span class="font-semibold">{{ $badge->valid_from?->format('M d, Y') ?? '—' }}</span></div>
                <div><span class="text-slate-400">Valid Until:</span> <span class="font-semibold">{{ $badge->valid_until?->format('M d, Y') ?? '—' }}</span></div>
                <div><span class="text-slate-400">Submitted:</span> <span class="font-semibold">{{ $badge->created_at->format('M d, Y H:i') }}</span></div>
                <div><span class="text-slate-400">Verification Code:</span> <span class="font-semibold">{{ $badge->verification_code ?? '—' }}</span></div>
            </div>
        </div>
    </div>

    {{-- Government ID --}}
    <div class="card">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 dark:text-slate-100 mb-3">Government ID Document</h3>
            @if($badge->government_id_path)
                <a href="{{ Storage::url($badge->government_id_path) }}" target="_blank">
                    <img src="{{ Storage::url($badge->government_id_path) }}" class="max-h-96 rounded-lg border border-slate-200 dark:border-slate-700 mx-auto">
                </a>
                <p class="text-center text-xs text-slate-400 mt-2">Click to view full size</p>
            @elseif($badge->document_path)
                <a href="{{ Storage::url($badge->document_path) }}" target="_blank">
                    <img src="{{ Storage::url($badge->document_path) }}" class="max-h-96 rounded-lg border border-slate-200 dark:border-slate-700 mx-auto">
                </a>
            @else
                <p class="text-center text-slate-400 py-4">No document uploaded.</p>
            @endif
        </div>
    </div>

    @if($badge->status === 'pending_review')
        <div class="card">
            <div class="card-body">
                <h3 class="font-bold text-slate-800 dark:text-slate-100 mb-3">Review Actions</h3>
                <div class="grid sm:grid-cols-2 gap-4">
                    <form action="{{ route('admin.verification.approve', $badge) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success w-full">✓ Approve (30-day validity)</button>
                    </form>
                    <form action="{{ route('admin.verification.reject', $badge) }}" method="POST">
                        @csrf
                        <label class="form-label">Rejection Reason</label>
                        <div class="flex gap-2">
                            <input type="text" name="reason" required class="form-input" placeholder="Reason">
                            <button type="submit" class="btn btn-danger flex-shrink-0">Reject</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @elseif($badge->status === 'verified')
        <div class="card border-red-200">
            <div class="card-body">
                <h3 class="font-bold text-red-600 mb-3">Revoke Verification</h3>
                <form action="{{ route('admin.verification.revoke', $badge) }}" method="POST">
                    @csrf
                    <div class="flex gap-2">
                        <input type="text" name="reason" required class="form-input" placeholder="Reason for revocation">
                        <button type="submit" class="btn btn-danger flex-shrink-0" onclick="return confirm('Revoke this user's verification badge?')">Revoke Badge</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection
