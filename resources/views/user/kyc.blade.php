@extends('layouts.user')

@section('title', 'KYC Verification')
@section('heading', 'Identity Verification (KYC)')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Info banner --}}
    <div class="card">
        <div class="card-body">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center flex-shrink-0">
                    <x-icon name="admin" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100">Verify Your Identity</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">KYC verification is required before you can withdraw earnings. Submit a government-issued ID and a selfie. Our team {{ $autoApprove ? 'will automatically review' : 'will manually review' }} your documents.</p>
                </div>
            </div>
        </div>
    </div>

    @if(!$enabled)
        <div class="card border-amber-300">
            <div class="card-body">
                <p class="text-amber-600 font-semibold flex items-center gap-2"><x-icon name="complaints" class="w-5 h-5" /> KYC verification is currently disabled by the administrator.</p>
            </div>
        </div>
    @elseif($submission)
        {{-- Existing submission status --}}
        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-slate-800 dark:text-slate-100">Your Submission</h3>
                    @if($submission->status === 'approved')
                        <span class="badge badge-success">✓ Approved</span>
                    @elseif($submission->status === 'rejected')
                        <span class="badge badge-danger">✕ Rejected</span>
                    @else
                        <span class="badge badge-warning">Pending Review</span>
                    @endif
                </div>

                <div class="grid sm:grid-cols-2 gap-4 text-sm">
                    <div><span class="text-slate-400">Full Name:</span> <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $submission->full_name }}</span></div>
                    <div><span class="text-slate-400">ID Type:</span> <span class="font-semibold capitalize text-slate-700 dark:text-slate-200">{{ str_replace('_',' ',$submission->id_type) }}</span></div>
                    <div><span class="text-slate-400">ID Number:</span> <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $submission->id_number }}</span></div>
                    <div><span class="text-slate-400">Country:</span> <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $submission->country }}</span></div>
                    <div><span class="text-slate-400">Date of Birth:</span> <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $submission->date_of_birth?->format('M d, Y') }}</span></div>
                    <div><span class="text-slate-400">Submitted:</span> <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $submission->created_at?->format('M d, Y H:i') }}</span></div>
                </div>

                @if($submission->status === 'rejected' && $submission->rejection_reason)
                    <div class="mt-4 p-3 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800">
                        <p class="text-sm text-red-700 dark:text-red-300"><strong>Rejection reason:</strong> {{ $submission->rejection_reason }}</p>
                    </div>
                @endif

                {{-- Document previews --}}
                <div class="mt-4 grid sm:grid-cols-3 gap-3">
                    @if($submission->document_front)
                        <div>
                            <p class="text-xs text-slate-400 mb-1">Document Front</p>
                            <a href="{{ Storage::url($submission->document_front) }}" target="_blank" class="block rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700 hover:border-blue-400">
                                <img src="{{ Storage::url($submission->document_front) }}" class="w-full h-32 object-cover" alt="ID Front">
                            </a>
                        </div>
                    @endif
                    @if($submission->document_back)
                        <div>
                            <p class="text-xs text-slate-400 mb-1">Document Back</p>
                            <a href="{{ Storage::url($submission->document_back) }}" target="_blank" class="block rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700 hover:border-blue-400">
                                <img src="{{ Storage::url($submission->document_back) }}" class="w-full h-32 object-cover" alt="ID Back">
                            </a>
                        </div>
                    @endif
                    @if($submission->selfie)
                        <div>
                            <p class="text-xs text-slate-400 mb-1">Selfie</p>
                            <a href="{{ Storage::url($submission->selfie) }}" target="_blank" class="block rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700 hover:border-blue-400">
                                <img src="{{ Storage::url($submission->selfie) }}" class="w-full h-32 object-cover" alt="Selfie">
                            </a>
                        </div>
                    @endif
                </div>

                @if($submission->status === 'rejected')
                    <div class="mt-4">
                        <a href="{{ route('user.kyc') }}?new=1" class="btn btn-primary">Submit New Application</a>
                    </div>
                @endif
            </div>
        </div>
    @elseif(!request('new'))
        {{-- No submission yet: prompt --}}
        <div class="card">
            <div class="card-body text-center py-8">
                <div class="w-16 h-16 rounded-full bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center mx-auto mb-4">
                    <x-icon name="profile" class="w-8 h-8 text-blue-600 dark:text-blue-400" />
                </div>
                <h3 class="font-bold text-slate-800 dark:text-slate-100 mb-2">No KYC Submission Yet</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Complete identity verification to unlock withdrawals and increase trust on the platform.</p>
                <a href="{{ route('user.kyc') }}?new=1" class="btn btn-primary">Start Verification</a>
            </div>
        </div>
    @else
        {{-- Submission form --}}
        <div class="card">
            <div class="card-body">
                <h3 class="font-bold text-slate-800 dark:text-slate-100 mb-4">Submit Your Documents</h3>
                <form action="{{ route('user.kyc.submit') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Full Legal Name *</label>
                            <input type="text" name="full_name" required class="form-input" placeholder="John Doe" value="{{ old('full_name') }}">
                        </div>
                        <div>
                            <label class="form-label">ID Type *</label>
                            <select name="id_type" required class="form-input">
                                <option value="">Select...</option>
                                <option value="national_id" @selected(old('id_type')==='national_id')>National ID</option>
                                <option value="passport" @selected(old('id_type')==='passport')>Passport</option>
                                <option value="drivers_license" @selected(old('id_type')==='drivers_license')>Driver's License</option>
                                <option value="voters_card" @selected(old('id_type')==='voters_card')>Voter's Card</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">ID Number *</label>
                            <input type="text" name="id_number" required class="form-input" placeholder="ID number" value="{{ old('id_number') }}">
                        </div>
                        <div>
                            <label class="form-label">Date of Birth *</label>
                            <input type="date" name="date_of_birth" required class="form-input" value="{{ old('date_of_birth') }}">
                        </div>
                        <div>
                            <label class="form-label">Country *</label>
                            <input type="text" name="country" required class="form-input" placeholder="Nigeria" value="{{ old('country') }}">
                        </div>
                        <div>
                            <label class="form-label">Address *</label>
                            <input type="text" name="address" required class="form-input" placeholder="Street, City, State" value="{{ old('address') }}">
                        </div>
                    </div>

                    <div class="grid sm:grid-cols-3 gap-4">
                        <div>
                            <label class="form-label">Document Front *</label>
                            <input type="file" name="document_front" required accept="image/*" class="form-input text-sm" onchange="previewImage(this,'prev-front')">
                            <img id="prev-front" class="mt-2 w-full h-28 object-cover rounded-lg hidden">
                        </div>
                        <div>
                            <label class="form-label">Document Back</label>
                            <input type="file" name="document_back" accept="image/*" class="form-input text-sm" onchange="previewImage(this,'prev-back')">
                            <img id="prev-back" class="mt-2 w-full h-28 object-cover rounded-lg hidden">
                        </div>
                        <div>
                            <label class="form-label">Selfie Photo *</label>
                            <input type="file" name="selfie" required accept="image/*" class="form-input text-sm" onchange="previewImage(this,'prev-selfie')">
                            <img id="prev-selfie" class="mt-2 w-full h-28 object-cover rounded-lg hidden">
                        </div>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit" class="btn btn-primary">Submit for Verification</button>
                        <a href="{{ route('user.kyc') }}" class="btn btn-ghost">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
function previewImage(input, targetId) {
    const img = document.getElementById(targetId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = (e) => { img.src = e.target.result; img.classList.remove('hidden'); };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
@endsection
