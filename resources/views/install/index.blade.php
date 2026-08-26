@extends('layouts.install')

@section('title', 'Install')

@section('content')
<div class="text-center mb-6">
    <h2 class="text-xl font-bold text-slate-800 mb-1">Install {{ config('app.name', 'Airtrendmedia') }}</h2>
    <p class="text-slate-500 text-sm">Enter your <strong>database details</strong> and <strong>admin details</strong> below. Everything else is set up automatically.</p>
</div>

@php
    $installError = session('install_error');
@endphp

@if($installError)
<div class="mb-5 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm flex items-start gap-2">
    <span>⚠️</span>
    <div>
        <p class="font-semibold mb-0.5">Installation could not complete</p>
        <p>{{ $installError }}</p>
        <p class="mt-1 text-red-600 text-xs">Fix the issue above and click <strong>Install Now</strong> again. Your details are kept.</p>
    </div>
</div>
@endif

{{-- Requirements summary --}}
<div class="mb-5 rounded-lg border border-slate-200 overflow-hidden">
    <button type="button" onclick="document.getElementById('reqPanel').classList.toggle('hidden')" class="w-full flex items-center justify-between px-4 py-3 bg-slate-50 text-left">
        <span class="text-sm font-semibold text-slate-700 flex items-center gap-2">
            <span>Server Requirements</span>
            @if($allPassed)
                <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700">{{ $passed }}/{{ $total }} passed</span>
            @else
                <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">{{ $passed }}/{{ $total }} passed</span>
            @endif
        </span>
        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </button>
    <div id="reqPanel" class="hidden px-4 py-3 space-y-1.5 bg-white">
        @foreach($checks as $label => $ok)
            <div class="flex items-center justify-between text-sm py-1">
                <span class="text-slate-600">{{ $label }}</span>
                @if($ok)
                    <span class="inline-flex items-center gap-1 text-green-600 font-medium">✓ OK</span>
                @else
                    <span class="inline-flex items-center gap-1 text-red-600 font-medium">✕ Missing</span>
                @endif
            </div>
        @endforeach
    </div>
</div>

@if(!$allPassed)
<div class="mb-5 px-4 py-3 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-xs">
    Some requirements are missing. You can still try to install, but if it fails, ask your hosting provider to enable the missing PHP extensions.
</div>
@endif

<form action="{{ route('install.process') }}" method="POST" id="installForm">
    @csrf

    {{-- ===== DATABASE SECTION ===== --}}
    <div class="mb-6">
        <h3 class="font-semibold text-slate-700 text-sm mb-3 flex items-center gap-2">
            <span class="w-6 h-6 rounded auth-gradient text-white text-xs flex items-center justify-center font-bold">1</span>
            Database Details
        </h3>
        <p class="text-xs text-slate-500 mb-3">Create an empty MySQL database in your hosting panel first, then enter its details here.</p>

        <div class="grid sm:grid-cols-2 gap-4 mb-3">
            <div>
                <label class="label">Database Host <span class="text-red-500">*</span></label>
                <input type="text" name="db_host" class="input" value="{{ old('db_host', '127.0.0.1') }}" required placeholder="127.0.0.1">
            </div>
            <div>
                <label class="label">Database Port <span class="text-red-500">*</span></label>
                <input type="number" name="db_port" class="input" value="{{ old('db_port', '3306') }}" required placeholder="3306">
            </div>
        </div>
        <div class="mb-3">
            <label class="label">Database Name <span class="text-red-500">*</span></label>
            <input type="text" name="db_database" class="input" value="{{ old('db_database') }}" required placeholder="airtrendmedia">
        </div>
        <div class="grid sm:grid-cols-2 gap-4 mb-3">
            <div>
                <label class="label">Database Username <span class="text-red-500">*</span></label>
                <input type="text" name="db_username" class="input" value="{{ old('db_username') }}" required placeholder="root">
            </div>
            <div>
                <label class="label">Database Password</label>
                <input type="password" name="db_password" class="input" value="{{ old('db_password') }}" placeholder="••••••••">
            </div>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="label">App / Site Name <span class="text-red-500">*</span></label>
                <input type="text" name="app_name" class="input" value="{{ old('app_name', 'Airtrendmedia') }}" required>
            </div>
            <div>
                <label class="label">App URL <span class="text-red-500">*</span></label>
                <input type="url" name="app_url" class="input" value="{{ old('app_url', $appUrl) }}" required>
            </div>
        </div>
    </div>

    <div class="border-t border-slate-100 my-6"></div>

    {{-- ===== ADMIN SECTION ===== --}}
    <div class="mb-6">
        <h3 class="font-semibold text-slate-700 text-sm mb-3 flex items-center gap-2">
            <span class="w-6 h-6 rounded auth-gradient text-white text-xs flex items-center justify-center font-bold">2</span>
            Admin Account Details
        </h3>
        <p class="text-xs text-slate-500 mb-3">This will be your super-administrator login. Choose a strong password (min 8 characters).</p>

        <div class="mb-3">
            <label class="label">Admin Full Name <span class="text-red-500">*</span></label>
            <input type="text" name="admin_name" class="input" value="{{ old('admin_name') }}" required placeholder="John Doe">
        </div>
        <div class="grid sm:grid-cols-2 gap-4 mb-3">
            <div>
                <label class="label">Admin Username <span class="text-red-500">*</span></label>
                <input type="text" name="admin_username" class="input" value="{{ old('admin_username') }}" required placeholder="admin" pattern="[A-Za-z0-9_.]+">
            </div>
            <div>
                <label class="label">Admin Email <span class="text-red-500">*</span></label>
                <input type="email" name="admin_email" class="input" value="{{ old('admin_email') }}" required placeholder="admin@example.com">
            </div>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="label">Admin Password <span class="text-red-500">*</span></label>
                <input type="password" name="admin_password" class="input" required minlength="8" placeholder="min 8 characters">
            </div>
            <div>
                <label class="label">Confirm Password <span class="text-red-500">*</span></label>
                <input type="password" name="admin_password_confirmation" class="input" required minlength="8" placeholder="retype password">
            </div>
        </div>
    </div>

    {{-- Validation errors (field-level) --}}
    @if($errors->any())
    <div class="mb-5 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
        <p class="font-semibold mb-1">Please fix the following:</p>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
        </ul>
    </div>
    @endif

    <div class="bg-blue-50 rounded-lg p-3 text-xs text-slate-600 mb-5">
        ℹ️ Clicking <strong>Install Now</strong> will create all database tables, seed default data, and set up your admin account. This takes a few seconds. Payment keys (Paystack) and email settings are configured later from the admin panel.
    </div>

    <button type="submit" id="installBtn" class="btn btn-primary w-full text-base py-3">
        <span id="installBtnText">⚡ Install Now</span>
        <span id="installBtnSpinner" class="hidden">⏳ Installing… please wait (this can take up to a minute)</span>
    </button>
</form>

<script>
document.getElementById('installForm').addEventListener('submit', function() {
    var btn = document.getElementById('installBtn');
    var txt = document.getElementById('installBtnText');
    var spin = document.getElementById('installBtnSpinner');
    btn.disabled = true;
    btn.classList.add('opacity-75', 'cursor-not-allowed');
    txt.classList.add('hidden');
    spin.classList.remove('hidden');
});
</script>
@endsection
