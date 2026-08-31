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
                    <x-verified-badge size="w-10 h-10" />
                </div>
                <div>
                    <h2 class="text-xl font-bold">Get the Blue Badge</h2>
                    <p class="text-blue-100 text-sm mt-1">Verified accounts get a blue badge next to their name, increased visibility, and higher trust. Requires a government ID and a monthly subscription of {{ money((float)$monthlyFee) }}. Valid for 30 days.</p>
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
                    <x-verified-badge size="w-12 h-12" />
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
                                Renew for 30 Days ({{ money((float)$monthlyFee) }})
                            </button>
                        </form>
                        @if($userBalance < $monthlyFee)
                            <p class="text-xs text-red-500 mt-2">Insufficient balance. Please top up your wallet.</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @elseif($badge && $badge->status === 'pending')
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
        <div class="card border-blue-200 bg-blue-50/50">
            <div class="card-body flex flex-col sm:flex-row gap-4 items-center justify-between">
                <div><h3 class="font-bold text-blue-800">Try the Blue Badge free for {{ $trialDays }} days</h3><p class="text-sm text-slate-600 mt-1">Start one free verification preview. No wallet deduction during the trial; after it expires you can apply for the paid badge.</p></div>
                <form action="{{ route('user.verification.trial') }}" method="POST">@csrf<button class="btn btn-primary whitespace-nowrap" type="submit">Start {{ $trialDays }}-Day Trial</button></form>
            </div>
        </div>
        @include('user.partials.verification-form', ['monthlyFee' => $monthlyFee, 'userBalance' => $userBalance])
    @endif

    {{-- Benefits --}}
    <div class="card">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 dark:text-slate-100 mb-3">Verification Benefits</h3>
            <div class="grid sm:grid-cols-2 gap-3 text-sm">
                <div class="flex items-center gap-2"><x-icon name="check" class="w-4 h-4 text-blue-600" /> Blue verified badge on profile & posts</div>
                <div class="flex items-center gap-2"><x-icon name="check" class="w-4 h-4 text-blue-600" /> Increased feed visibility & reach</div>
                <div class="flex items-center gap-2"><x-icon name="check" class="w-4 h-4 text-blue-600" /> Higher trust for monetization</div>
                <div class="flex items-center gap-2"><x-icon name="check" class="w-4 h-4 text-blue-600" /> Priority support</div>
            </div>
        </div>
    </div>
</div>
@endsection
