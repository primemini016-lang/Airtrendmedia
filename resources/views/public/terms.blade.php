@extends('layouts.app')
@section('title', 'Terms & Conditions — Microjob Marketplace')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 py-10">
    <h1 class="text-3xl font-bold text-slate-800 mb-6">Terms & Conditions</h1>
    <div class="card"><div class="card-body prose prose-slate max-w-none text-sm leading-relaxed space-y-4">
        <p>Welcome to Airtrendmedia. By registering an account and using our platform, you agree to the following terms and conditions. Please read them carefully.</p>
        <h3 class="font-bold text-slate-800">1. Account Activation</h3>
        <p>To perform tasks and earn money on the platform, you must pay a one-time account activation fee. This fee is non-refundable and grants you permanent access to the marketplace as a worker.</p>
        <h3 class="font-bold text-slate-800">2. Task Completion</h3>
        <p>When you book a task, you must complete it and submit valid proof before the deadline expires. Employers review your proof and either approve (paying you instantly) or reject it (freeing the slot for another worker).</p>
        <h3 class="font-bold text-slate-800">3. Prohibited Conduct</h3>
        <p>You may not submit fake or plagiarized proofs, create multiple accounts to manipulate tasks, or attempt to defraud employers or the platform. Violations result in account suspension and forfeiture of balances.</p>
        <h3 class="font-bold text-slate-800">4. Payments & Withdrawals</h3>
        <p>All payments are processed through Paystack. Withdrawal requests are subject to an administrative fee and are processed within the timeframe set by the administrator. You are responsible for providing accurate withdrawal details.</p>
        <h3 class="font-bold text-slate-800">5. Affiliate Program</h3>
        <p>Affiliate rewards are paid only for referrals who successfully pay the activation fee. The platform reserves the right to revoke rewards obtained through fraudulent or abusive referral practices.</p>
        <h3 class="font-bold text-slate-800">6. Account Termination</h3>
        <p>The platform may suspend or terminate any account that violates these terms. Banned accounts may not re-register. Balances associated with fraudulent activity may be forfeited.</p>
        <h3 class="font-bold text-slate-800">7. Changes to Terms</h3>
        <p>We reserve the right to update these terms at any time. Continued use of the platform after changes constitutes acceptance of the new terms.</p>
        <p class="text-slate-400">Last updated: {{ date('F j, Y') }}</p>
    </div></div>
</div>
@endsection
