@extends('layouts.user')

@section('title', 'Blue Verification Badge')
@section('heading', 'Blue Verification Badge')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="card bg-gradient-to-r from-blue-600 to-blue-800 text-white">
        <div class="card-body">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0">
                    <svg width="28" height="28" fill="currentColor" viewBox="0 0 24 24"><path d="M22.5 12.5c0-1.58-.875-2.95-2.148-3.6.154-.435.238-.905.238-1.4 0-2.21-1.71-3.998-3.818-3.998-.47 0-.92.084-1.336.25C14.818 2.415 13.51 1.5 12 1.5s-2.816.917-3.437 2.25c-.415-.165-.866-.25-1.336-.25-2.11 0-3.818 1.79-3.818 4 0 .494.083.964.237 1.4-1.272.65-2.147 2.018-2.147 3.6 0 1.495.782 2.798 1.942 3.486-.02.17-.032.34-.032.514 0 2.21 1.708 4 3.818 4 .47 0 .92-.086 1.335-.25.62 1.334 1.926 2.25 3.437 2.25 1.512 0 2.818-.916 3.437-2.25.415.163.865.248 1.336.248 2.11 0 3.818-1.79 3.818-4 0-.174-.012-.344-.033-.513 1.158-.687 1.943-1.99 1.943-3.484zm-6.616-3.334l-4.334 6.5c-.145.217-.382.334-.625.334-.143 0-.288-.04-.416-.126l-.115-.094-2.415-2.415c-.293-.293-.293-.768 0-1.06s.768-.294 1.06 0l1.77 1.767 3.825-5.74c.23-.345.696-.436 1.04-.207.345.23.44.696.21 1.04z"/></svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold">Get the Blue Badge</h2>
                    <p class="text-blue-100 text-sm mt-1">Verified accounts get a blue badge next to their name, increased visibility, and higher trust. Requires a government ID and a monthly subscription of ${{ number_format($monthlyFee, 2) }}. Valid for 30 days.</p>
                </div>
            </div>
        </div>
    </div>

    @if(!$enabled)
        <div class="card border-amber-300">
            <div class="card-body">
                <p class="text-amber-600 font-semibold flex items-center gap-2"><x-icon name="complaints" class="w-5 h-5" /> Blue verification is currently disabled by the administrator.</p>
            </div>
        </div>
    @elseif($isBlue)
        {{-- Already verified --}}
        <div class="card">
            <div class="card-body text-center py-8">
                <div class="w-20 h-20 rounded-full bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center mx-auto mb-4">
                    <svg width="40" height="40" fill="currentColor" viewBox="0 0 24 24" class="text-blue-600"><path d="M22.5 12.5c0-1.58-.875-2.95-2.148-3.6.154-.435.238-.905.238-1.4 0-2.21-1.71-3.998-3.818-3.998-.47 0-.92.084-1.336.25C14.818 2.415 13.51 1.5 12 1.5s-2.816.917-3.437 2.25c-.415-.165-.866-.25-1.336-.25-2.11 0-3.818 1.79-3.818 4 0 .494.083.964.237 1.4-1.272.65-2.147 2.018-2.147 3.6 0 1.495.782 2.798 1.942 3.486-.02.17-.032.34-.032.514 0 2.21 1.708 4 3.818 4 .47 0 .92-.086 1.335-.25.62 1.334 1.926 2.25 3.437 2.25 1.512 0 2.818-.916 3.437-2.25.415.163.865.248 1.336.248 2.11 0 3.818-1.79 3.818-4 0-.174-.012-.344-.033-.513 1.158-.687 1.943-1.99 1.943-3.484zm-6.616-3.334l-4.334 6.5c-.145.217-.382.334-.625.334-.143 0-.288-.04-.416-.126l-.115-.094-2.415-2.415c-.293-.293-.293-.768 0-1.06s.768-.294 1.06 0l1.77 1.767 3.825-5.74c.23-.345.696-.436 1.04-.207.345.23.44.696.21 1.04z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-blue-600 dark:text-blue-400">You're Verified!</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Your blue verification badge is active.</p>
                <div class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-full bg-amber-50 dark:bg-amber-900/30 border border-amber-200">
                    <x-icon name="star" class="w-4 h-4 text-amber-500" />
                    <span class="text-sm font-semibold text-amber-700 dark:text-amber-300">Expires in {{ $daysLeft }} day{{ $daysLeft === 1 ? '' : 's' }}</span>
                </div>
                @if($badge && $badge->valid_until)
                    <p class="text-xs text-slate-400 mt-2">Valid until: {{ $badge->valid_until->format('M d, Y') }}</p>
                @endif

                @if($daysLeft <= 7)
                    <div class="mt-6 max-w-sm mx-auto">
                        <form action="{{ route('user.verification.renew') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary w-full" @if($userBalance < $monthlyFee) disabled @endif>
                                Renew for 30 Days (${{ number_format($monthlyFee, 2) }})
                            </button>
                        </form>
                        @if($userBalance < $monthlyFee)
                            <p class="text-xs text-red-500 mt-2">Insufficient balance. Please top up your wallet.</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @elseif($badge && $badge->status === 'pending_review')
        <div class="card">
            <div class="card-body text-center py-8">
                <span class="badge badge-warning text-base">Pending Review</span>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-3">Your verification request is being reviewed. This may take up to 48 hours.</p>
                <p class="text-xs text-slate-400 mt-2">Submitted on {{ $badge->created_at->format('M d, Y H:i') }}</p>
            </div>
        </div>
    @elseif($badge && $badge->status === 'rejected')
        <div class="card border-red-200">
            <div class="card-body">
                <div class="flex items-center gap-3 mb-3">
                    <span class="badge badge-danger">Rejected</span>
                    <span class="text-sm text-slate-500">You can reapply below.</span>
                </div>
                @if($badge->rejection_reason)
                    <p class="text-sm text-red-600 dark:text-red-400 p-3 rounded-lg bg-red-50 dark:bg-red-900/30">{{ $badge->rejection_reason }}</p>
                @endif
            </div>
        </div>
        @include('user.partials.verification-form', ['monthlyFee' => $monthlyFee, 'userBalance' => $userBalance])
    @else
        {{-- Apply form --}}
        @include('user.partials.verification-form', ['monthlyFee' => $monthlyFee, 'userBalance' => $userBalance])
    @endif

    {{-- Benefits --}}
    <div class="card">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 dark:text-slate-100 mb-3">Verification Benefits</h3>
            <div class="grid sm:grid-cols-2 gap-3 text-sm">
                <div class="flex items-center gap-2"><span class="text-blue-600">✓</span> Blue verified badge on profile & posts</div>
                <div class="flex items-center gap-2"><span class="text-blue-600">✓</span> Increased feed visibility & reach</div>
                <div class="flex items-center gap-2"><span class="text-blue-600">✓</span> Higher trust for monetization</div>
                <div class="flex items-center gap-2"><span class="text-blue-600">✓</span> Priority support</div>
            </div>
        </div>
    </div>
</div>
@endsection
