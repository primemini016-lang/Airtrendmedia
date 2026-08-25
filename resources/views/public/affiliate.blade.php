@extends('layouts.app')
@section('title', 'Affiliate Program — Microjob Marketplace')

@section('content')
<!-- Hero -->
<section class="auth-gradient text-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-16 text-center">
        <span class="badge bg-white/20 text-white mb-4">🤝 Affiliate Program</span>
        <h1 class="text-3xl sm:text-5xl font-extrabold">Earn ${{ number_format((float)($s->affiliate_reward ?? 1.50),2) }} Per Referral</h1>
        <p class="mt-4 text-lg text-blue-100 max-w-2xl mx-auto">Invite friends to join the marketplace. When they register and pay the ${{ number_format((float)($s->activation_fee ?? 5.00),2) }} activation fee, you earn ${{ number_format((float)($s->affiliate_reward ?? 1.50),2) }} — credited instantly to your wallet.</p>
        @guest<a href="{{ route('register') }}" class="btn bg-white text-blue-700 hover:bg-blue-50 mt-6">Join Now & Get Your Link</a>@endguest
    </div>
</section>

<!-- How it works -->
<section class="max-w-5xl mx-auto px-4 sm:px-6 py-14">
    <h2 class="text-2xl font-bold text-center text-slate-800 mb-10">How the Affiliate Program Works</h2>
    <div class="grid md:grid-cols-3 gap-6">
        <div class="card p-6 text-center">
            <div class="w-14 h-14 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-2xl mx-auto mb-4">🔗</div>
            <h3 class="font-bold text-slate-800 mb-2">Share Your Link</h3>
            <p class="text-sm text-slate-500">Get your unique referral link from your dashboard and share it with friends, on social media, or your blog.</p>
        </div>
        <div class="card p-6 text-center">
            <div class="w-14 h-14 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-2xl mx-auto mb-4">👥</div>
            <h3 class="font-bold text-slate-800 mb-2">They Register</h3>
            <p class="text-sm text-slate-500">When someone signs up using your link, they're tracked as your referral in real time.</p>
        </div>
        <div class="card p-6 text-center">
            <div class="w-14 h-14 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-2xl mx-auto mb-4">💰</div>
            <h3 class="font-bold text-slate-800 mb-2">You Earn ${{ number_format((float)($s->affiliate_reward ?? 1.50),2) }}</h3>
            <p class="text-sm text-slate-500">Once your referral pays the activation fee, your reward is credited instantly — no waiting.</p>
        </div>
    </div>
</section>

<!-- Features -->
<section class="bg-slate-50 py-14">
    <div class="max-w-5xl mx-auto px-4 sm:px-6">
        <h2 class="text-2xl font-bold text-center text-slate-800 mb-10">Why Join Our Affiliate Program?</h2>
        <div class="grid sm:grid-cols-2 gap-6">
            <div class="flex gap-4 items-start"><div class="w-10 h-10 rounded-lg bg-green-100 text-green-600 flex items-center justify-center flex-shrink-0">✓</div><div><h3 class="font-semibold text-slate-800">Instant Payouts</h3><p class="text-sm text-slate-500">Rewards are credited to your wallet automatically the moment a referral activates. No manual requests needed.</p></div></div>
            <div class="flex gap-4 items-start"><div class="w-10 h-10 rounded-lg bg-green-100 text-green-600 flex items-center justify-center flex-shrink-0">✓</div><div><h3 class="font-semibold text-slate-800">Real-Time Tracking</h3><p class="text-sm text-slate-500">A beautiful dashboard shows your referrals, their status, and your total earnings updated live.</p></div></div>
            <div class="flex gap-4 items-start"><div class="w-10 h-10 rounded-lg bg-green-100 text-green-600 flex items-center justify-center flex-shrink-0">✓</div><div><h3 class="font-semibold text-slate-800">Fraud-Protected</h3><p class="text-sm text-slate-500">Our system only rewards genuine, paid activations — protecting honest affiliates from abuse.</p></div></div>
            <div class="flex gap-4 items-start"><div class="w-10 h-10 rounded-lg bg-green-100 text-green-600 flex items-center justify-center flex-shrink-0">✓</div><div><h3 class="font-semibold text-slate-800">Unlimited Referrals</h3><p class="text-sm text-slate-500">There's no cap. Refer as many people as you like and watch your earnings grow.</p></div></div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="max-w-3xl mx-auto px-4 sm:px-6 py-14 text-center">
    @guest
    <h2 class="text-2xl font-bold text-slate-800 mb-3">Start Earning Today</h2>
    <p class="text-slate-500 mb-6">Create your free account and grab your referral link in seconds.</p>
    <a href="{{ route('register') }}" class="btn btn-primary">Create Free Account</a>
    @else
    <h2 class="text-2xl font-bold text-slate-800 mb-3">Your Affiliate Dashboard Awaits</h2>
    <p class="text-slate-500 mb-6">Track your referrals and earnings in real time.</p>
    <a href="{{ route('user.affiliate') }}" class="btn btn-primary">Go to Affiliate Dashboard</a>
    @endguest
</section>
@endsection
