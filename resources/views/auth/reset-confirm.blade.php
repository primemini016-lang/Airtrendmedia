@extends('layouts.app')
@section('title', 'Confirm Reset — Microjob Marketplace')

@section('content')
<div class="max-w-md mx-auto px-4 py-12">
    <div class="card">
        <div class="card-body text-center">
            <div class="inline-flex w-14 h-14 rounded-2xl bg-blue-50 items-center justify-center text-2xl mb-3"><x-icon name="lock" class="w-4 h-4 inline" /></div>
            <h1 class="text-2xl font-bold text-slate-800">Enter Reset Code</h1>
            <p class="text-slate-500 text-sm mb-6">Enter the code sent to <strong>{{ $email }}</strong> and your new password.</p>
            <form method="POST">
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">
                <div class="mb-4 text-left">
                    <label class="label">Reset Code</label>
                    <input type="text" name="code" maxlength="6" class="input text-center text-xl tracking-[0.4em] font-bold" placeholder="000000" required autofocus>
                </div>
                <div class="mb-4 text-left">
                    <label class="label">New Password</label>
                    <input type="password" name="password" class="input" required>
                </div>
                <div class="mb-5 text-left">
                    <label class="label">Confirm New Password</label>
                    <input type="password" name="password_confirmation" class="input" required>
                </div>
                <button class="btn btn-primary w-full">Reset Password</button>
            </form>
        </div>
    </div>
</div>
@endsection
