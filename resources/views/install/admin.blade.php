@extends('layouts.install')

@section('title', 'Admin Account')

@section('content')
<h2 class="text-xl font-bold text-slate-800 mb-1">Create Your Admin Account</h2>
<p class="text-slate-500 text-sm mb-5">This will be your super-administrator login. <strong>No demo credentials are pre-installed.</strong> Choose a strong password.</p>

<form action="{{ route('install.admin') }}" method="POST">
    @csrf
    <div class="mb-4"><label class="label">Full Name <span class="text-red-500">*</span></label><input type="text" name="name" class="input" value="{{ old('name') }}" required></div>
    <div class="grid sm:grid-cols-2 gap-4 mb-4">
        <div><label class="label">Username <span class="text-red-500">*</span></label><input type="text" name="username" class="input" value="{{ old('username') }}" required></div>
        <div><label class="label">Email <span class="text-red-500">*</span></label><input type="email" name="email" class="input" value="{{ old('email') }}" required></div>
    </div>
    <div class="grid sm:grid-cols-2 gap-4 mb-4">
        <div><label class="label">Password <span class="text-red-500">*</span></label><input type="password" name="password" class="input" required minlength="8"></div>
        <div><label class="label">Confirm Password <span class="text-red-500">*</span></label><input type="password" name="password_confirmation" class="input" required minlength="8"></div>
    </div>

    <div class="bg-blue-50 rounded-lg p-3 text-xs text-slate-600 mb-4">ℹ After this step, the <code>installed.json</code> marker file is created and the installer is locked. You can log in at <code>/admin/login</code>.</div>

    <div class="flex gap-3">
        <a href="{{ route('install.app') }}" class="btn btn-outline flex-1"><x-icon name="arrow-left" class="w-4 h-4 inline" /> Back</a>
        <button type="submit" class="btn btn-primary flex-1" onclick="return confirm('Create the admin account and finish installation?')">Create Admin & Finish <x-icon name="arrow-right" class="w-4 h-4 inline" /></button>
    </div>
</form>
@endsection
