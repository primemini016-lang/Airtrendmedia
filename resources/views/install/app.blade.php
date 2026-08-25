@extends('layouts.install')

@section('title', 'Email Settings')

@section('content')
<h2 class="text-xl font-bold text-slate-800 mb-1">Email (SMTP) Settings</h2>
<p class="text-slate-500 text-sm mb-5">Configure your email server so the platform can send verification codes and notifications. You can change these later in the admin panel under Email Settings.</p>

<div class="mb-5 rounded-lg border-2 border-blue-100 bg-blue-50 p-4">
    <div class="flex items-start gap-3">
        <span class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm flex-shrink-0"><x-icon name="payment" class="w-4 h-4 inline" /></span>
        <div>
            <h3 class="font-semibold text-blue-900 text-sm mb-1">Payment API Keys — Set After Installation</h3>
            <p class="text-blue-700 text-xs leading-relaxed">Payment gateway keys (Paystack public &amp; secret keys) are <strong>not</strong> configured during installation. Once setup is complete, log in to your Admin Dashboard and navigate to <strong>Settings <x-icon name="arrow-right" class="w-4 h-4 inline" /> Payment Keys</strong> to securely enter your API credentials.</p>
        </div>
    </div>
</div>

<form action="{{ route('install.app') }}" method="POST">
    @csrf
    <div class="border-t border-slate-100 pt-4">
        <h3 class="font-semibold text-slate-700 text-sm mb-3 flex items-center gap-2"><span class="w-6 h-6 rounded auth-gradient text-white text-xs flex items-center justify-center"><x-icon name="email" class="w-4 h-4 inline" /></span> Email (SMTP)</h3>
        <div class="grid sm:grid-cols-2 gap-4 mb-3">
            <div><label class="label">Mail Host</label><input type="text" name="mail_host" class="input" value="{{ old('mail_host') }}" placeholder="smtp.gmail.com"></div>
            <div><label class="label">Mail Port</label><input type="number" name="mail_port" class="input" value="{{ old('mail_port', '587') }}"></div>
        </div>
        <div class="grid sm:grid-cols-2 gap-4 mb-3">
            <div><label class="label">Mail Username</label><input type="text" name="mail_username" class="input" value="{{ old('mail_username') }}"></div>
            <div><label class="label">Mail Password</label><input type="password" name="mail_password" class="input" value="{{ old('mail_password') }}"></div>
        </div>
        <div class="grid sm:grid-cols-2 gap-4 mb-4">
            <div><label class="label">From Address</label><input type="email" name="mail_from_address" class="input" value="{{ old('mail_from_address', 'no-reply@airtrendmedia.com') }}"></div>
            <div><label class="label">From Name</label><input type="text" name="mail_from_name" class="input" value="{{ old('mail_from_name', 'MiniWorkers') }}"></div>
        </div>
    </div>

    <div class="flex gap-3">
        <a href="{{ route('install.database') }}" class="btn btn-outline flex-1"><x-icon name="arrow-left" class="w-4 h-4 inline" /> Back</a>
        <button type="submit" class="btn btn-primary flex-1">Save & Continue <x-icon name="arrow-right" class="w-4 h-4 inline" /></button>
    </div>
</form>
@endsection
