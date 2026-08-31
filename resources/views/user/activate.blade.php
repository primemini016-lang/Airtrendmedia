@extends('layouts.app')
@section('title', 'Activate Account')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 py-10">
    <div class="text-center mb-8">
        <div class="inline-flex w-16 h-16 rounded-2xl auth-gradient items-center justify-center text-white text-3xl mb-3"><x-icon name="zap" class="w-4 h-4 inline" /></div>
        <h1 class="text-3xl font-bold text-slate-800">Activate Your Account</h1>
        <p class="text-slate-500 mt-2">Pay the one-time activation fee to unlock the marketplace and start earning.</p>
    </div>

    <div class="card mb-6">
        <div class="card-body">
            <div class="flex items-center justify-between py-3 border-b border-slate-100">
                <span class="text-slate-600">Activation Fee</span>
                <span class="text-2xl font-bold text-blue-600">{{ money((float)$s->activation_fee) }}</span>
            </div>
            <div class="flex items-center justify-between py-3 border-b border-slate-100">
                <span class="text-slate-600">Charged Currency</span>
                <span class="font-semibold text-slate-800">{{ $currency->code }} (Paystack)</span>
            </div>
            <div class="flex items-center justify-between py-3">
                <span class="text-slate-600">You Will Pay</span>
                <span class="font-semibold text-slate-800">{{ money($currency->fromUsd((float)$s->activation_fee)) }} {{ $currency->code }}</span>
            </div>
        </div>
    </div>

    <div class="card bg-blue-50 border-blue-200 mb-6">
        <div class="card-body">
            <h3 class="font-bold text-blue-800 mb-2 flex items-center gap-2"><x-icon name="check" class="w-4 h-4 inline" /> What You Get</h3>
            <ul class="space-y-2 text-sm text-blue-900">
                <li class="flex gap-2"><span><x-icon name="check" class="w-4 h-4 inline" /></span> Permanent access to perform tasks and earn money</li>
                <li class="flex gap-2"><span><x-icon name="check" class="w-4 h-4 inline" /></span> Ability to post your own jobs/tasks for others</li>
                <li class="flex gap-2"><span><x-icon name="check" class="w-4 h-4 inline" /></span> Access to the affiliate program — earn {{ money((float)$s->affiliate_reward) }} per referral</li>
                <li class="flex gap-2"><span><x-icon name="check" class="w-4 h-4 inline" /></span> Full wallet, withdrawal, and support features</li>
            </ul>
        </div>
    </div>

    @if($pendingDeposit)
    <div class="card bg-amber-50 border-amber-200 mb-6">
        <div class="card-body text-sm text-amber-800">
            You have a pending activation payment (ref: {{ $pendingDeposit->reference }}). If you already paid, click verify below.
            <form method="POST" action="{{ route('user.activate.verify') }}" class="mt-3">
                @csrf
                <input type="hidden" name="reference" value="{{ $pendingDeposit->reference }}">
                <button class="btn btn-primary text-xs">Verify Payment</button>
            </form>
        </div>
    </div>
    @endif

    <form method="POST" action="{{ route('user.activate') }}/initiate">
        @csrf
        <button class="btn btn-primary w-full text-base py-3">Pay {{ money((float)$s->activation_fee) }} & Activate Now</button>
    </form>

    {{-- 3-Day Free Trial Option --}}
    <div class="relative my-6">
        <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200"></div></div>
        <div class="relative flex justify-center"><span class="bg-white px-4 text-sm text-slate-400">or</span></div>
    </div>

    <div class="card bg-gradient-to-br from-green-50 to-emerald-50 border-green-200 mb-6">
        <div class="card-body text-center">
            <div class="inline-flex w-12 h-12 rounded-xl bg-green-500 items-center justify-center text-white mb-3">
                <x-icon name="clock" class="w-6 h-6" />
            </div>
            <h3 class="font-bold text-green-800 text-lg mb-1">3-Day Free Trial</h3>
            <p class="text-sm text-green-700 mb-4">Can't pay right now? Try everything free for 3 days. After your trial ends, pay the {{ money((float)$s->activation_fee) }} fee to keep your access.</p>
            <form method="POST" action="{{ route('user.activate.trial') }}">
                @csrf
                <button class="btn w-full text-base py-3 bg-green-500 hover:bg-green-600 text-white border-0">Start 3-Day Free Trial</button>
            </form>
            @if(auth('web')->user()?->isOnTrial())
                <p class="text-xs text-green-600 mt-3 font-semibold">Trial active — {{ auth('web')->user()->trialDaysLeft() }} day(s) remaining</p>
            @endif
        </div>
    </div>

    <p class="text-center text-xs text-slate-400 mt-4"><x-icon name="lock" class="w-4 h-4 inline" /> Secure payment powered by Paystack. Your payment is verified server-side for your protection.</p>
</div>
@endsection
