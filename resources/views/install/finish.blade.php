@extends('layouts.install')

@section('title', 'Installation Complete')

@section('content')
<div class="text-center">
    <div class="w-20 h-20 mx-auto rounded-full bg-green-100 flex items-center justify-center mb-4">
        <svg width="40" height="40" fill="none" stroke="#16a34a" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
    </div>
    <h2 class="text-2xl font-bold text-slate-800 mb-2"><x-icon name="check-circle" class="w-7 h-7 inline text-green-500" /> Installation Complete!</h2>
    <p class="text-slate-500 text-sm mb-1">Your <strong>{{ $appName }}</strong> platform is ready to go.</p>
    @if($adminEmail)
    <p class="text-slate-400 text-xs mb-6">Admin account created for <code>{{ $adminEmail }}</code></p>
    @else
    <p class="text-slate-400 text-xs mb-6">&nbsp;</p>
    @endif

    <div class="text-left bg-slate-50 rounded-lg p-4 mb-6 space-y-3 text-sm">
        <div class="flex items-start gap-3">
            <span class="w-6 h-6 rounded-full auth-gradient text-white text-xs flex items-center justify-center flex-shrink-0 font-bold">1</span>
            <div><p class="font-semibold text-slate-700">Log in to your admin panel</p><p class="text-slate-500 text-xs">Go to <a href="{{ route('admin.login') }}" class="text-blue-600 underline">{{ route('admin.login') }}</a> and sign in with the credentials you just created.</p></div>
        </div>
        <div class="flex items-start gap-3">
            <span class="w-6 h-6 rounded-full auth-gradient text-white text-xs flex items-center justify-center flex-shrink-0 font-bold">2</span>
            <div><p class="font-semibold text-slate-700">Set your payment keys</p><p class="text-slate-500 text-xs">In <strong>Admin → Settings → Payment Keys</strong>, enter your Paystack public &amp; secret keys.</p></div>
        </div>
        <div class="flex items-start gap-3">
            <span class="w-6 h-6 rounded-full auth-gradient text-white text-xs flex items-center justify-center flex-shrink-0 font-bold">3</span>
            <div><p class="font-semibold text-slate-700">Configure currencies &amp; email</p><p class="text-slate-500 text-xs">Set your default currency in <strong>Admin → Currencies</strong> and SMTP in <strong>Admin → Email Settings</strong>.</p></div>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row gap-3">
        <a href="{{ route('admin.login') }}" class="btn btn-primary flex-1">Go to Admin Panel →</a>
        <a href="{{ route('home') }}" class="btn btn-outline flex-1">View Your Site</a>
    </div>
</div>
@endsection
