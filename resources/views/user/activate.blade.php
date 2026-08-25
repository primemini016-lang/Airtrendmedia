@extends('layouts.app')
@section('title', 'Activate Account')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 py-10">
    <div class="text-center mb-8">
        <div class="inline-flex w-16 h-16 rounded-2xl auth-gradient items-center justify-center text-white text-3xl mb-3">⚡</div>
        <h1 class="text-3xl font-bold text-slate-800">Activate Your Account</h1>
        <p class="text-slate-500 mt-2">Pay the one-time activation fee to unlock the marketplace and start earning.</p>
    </div>

    <div class="card mb-6">
        <div class="card-body">
            <div class="flex items-center justify-between py-3 border-b border-slate-100">
                <span class="text-slate-600">Activation Fee</span>
                <span class="text-2xl font-bold text-blue-600">${{ number_format((float)$s->activation_fee,2) }} <span class="text-sm text-slate-400">USD</span></span>
            </div>
            <div class="flex items-center justify-between py-3 border-b border-slate-100">
                <span class="text-slate-600">Charged Currency</span>
                <span class="font-semibold text-slate-800">{{ $currency->code }} (Paystack)</span>
            </div>
            <div class="flex items-center justify-between py-3">
                <span class="text-slate-600">You Will Pay</span>
                <span class="font-semibold text-slate-800">{{ $currency->symbol }}{{ number_format($currency->fromUsd((float)$s->activation_fee),2) }} {{ $currency->code }}</span>
            </div>
        </div>
    </div>

    <div class="card bg-blue-50 border-blue-200 mb-6">
        <div class="card-body">
            <h3 class="font-bold text-blue-800 mb-2 flex items-center gap-2">✓ What You Get</h3>
            <ul class="space-y-2 text-sm text-blue-900">
                <li class="flex gap-2"><span>✓</span> Permanent access to perform tasks and earn money</li>
                <li class="flex gap-2"><span>✓</span> Ability to post your own jobs/tasks for others</li>
                <li class="flex gap-2"><span>✓</span> Access to the affiliate program — earn ${{ number_format((float)$s->affiliate_reward,2) }} per referral</li>
                <li class="flex gap-2"><span>✓</span> Full wallet, withdrawal, and support features</li>
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
        <button class="btn btn-primary w-full text-base py-3">Pay ${{ number_format((float)$s->activation_fee,2) }} & Activate Now</button>
    </form>

    <p class="text-center text-xs text-slate-400 mt-4">🔒 Secure payment powered by Paystack. Your payment is verified server-side for your protection.</p>
</div>
@endsection
