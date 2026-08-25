@extends('layouts.admin')

@section('title', 'Payment Keys')
@section('heading', 'Payment / Payout API Keys')

@section('content')
<div class="mb-6">
    <p class="text-slate-500 text-sm">Configure your Paystack API keys here. These are used for activation fees, deposits, and payouts. Keys are stored securely and written to your .env file.</p>
</div>

<div class="card max-w-2xl">
    <div class="card-body">
        <h3 class="font-bold text-slate-800 mb-4">Paystack Configuration</h3>
        <form action="{{ route('admin.payment-keys.update') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="label">Public Key</label>
                <input type="text" name="paystack_public_key" class="input font-mono text-xs" value="{{ $keys['paystack_public_key'] ?? '' }}" placeholder="pk_test_xxxxxxxxxxxxxxxxxxxx">
            </div>
            <div class="mb-3">
                <label class="label">Secret Key</label>
                <input type="password" name="paystack_secret_key" class="input font-mono text-xs" value="{{ $keys['paystack_secret_key'] ?? '' }}" placeholder="sk_test_xxxxxxxxxxxxxxxxxxxx">
            </div>
            <div class="mb-3">
                <label class="label">Base URL</label>
                <input type="text" name="paystack_base_url" class="input font-mono text-xs" value="{{ $keys['paystack_base_url'] ?? 'https://api.paystack.co' }}">
            </div>
            <div class="grid sm:grid-cols-2 gap-4 mb-3">
                <div>
                    <label class="label">Currency</label>
                    <input type="text" name="paystack_currency" class="input" value="{{ $keys['paystack_currency'] ?? 'NGN' }}" placeholder="NGN">
                </div>
                <div>
                    <label class="label">Callback URL</label>
                    <input type="text" name="paystack_callback_url" class="input" value="{{ $keys['paystack_callback_url'] ?? '' }}" placeholder="https://airtrendmedia.com/api/payment/callback">
                </div>
            </div>
            <button class="btn btn-primary">Save Payment Keys</button>
        </form>
    </div>
</div>

<div class="card max-w-2xl mt-6">
    <div class="card-body">
        <h4 class="font-bold text-slate-800 mb-2">Where to get your keys</h4>
        <p class="text-sm text-slate-600">Log in to your <a href="https://dashboard.paystack.com/settings/developer" target="_blank" class="text-blue-600 hover:underline">Paystack Dashboard <x-icon name="arrow-right" class="w-4 h-4 inline" /> Settings <x-icon name="arrow-right" class="w-4 h-4 inline" /> API Keys &amp; Webhooks</a>. Use test keys for development and live keys for production.</p>
    </div>
</div>
@endsection
