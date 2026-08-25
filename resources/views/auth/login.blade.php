@extends('layouts.app')
@section('title', 'Login — Microjob Marketplace')

@section('content')
<div class="max-w-md mx-auto px-4 py-12">
    <div class="card">
        <div class="card-body">
            <div class="text-center mb-6">
                <div class="inline-flex w-14 h-14 rounded-2xl auth-gradient items-center justify-center text-white text-2xl font-bold mb-3">M</div>
                <h1 class="text-2xl font-bold text-slate-800">Welcome Back</h1>
                <p class="text-slate-500 text-sm">Sign in to your account to continue</p>
            </div>
            <form method="POST">
                @csrf
                <div class="mb-4">
                    <label class="label">Username or Email</label>
                    <input type="text" name="username" value="{{ old('username') }}" class="input" required autofocus>
                </div>
                <div class="mb-4">
                    <label class="label">Password</label>
                    <input type="password" name="password" class="input" required>
                </div>
                <div class="flex items-center justify-between mb-5">
                    <label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="remember" class="rounded"> Remember me</label>
                    <a href="{{ route('password.request') }}" class="text-sm text-blue-600 hover:underline">Forgot password?</a>
                </div>
                <button class="btn btn-primary w-full">Sign In</button>
            </form>
            <p class="text-center text-sm text-slate-500 mt-5">Don't have an account? <a href="{{ route('register') }}" class="text-blue-600 font-semibold hover:underline">Sign up free</a></p>
        </div>
    </div>
</div>
@endsection
