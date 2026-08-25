@extends('layouts.app')
@section('title', 'Reset Password — Microjob Marketplace')

@section('content')
<div class="max-w-md mx-auto px-4 py-12">
    <div class="card">
        <div class="card-body text-center">
            <div class="inline-flex w-14 h-14 rounded-2xl bg-blue-50 items-center justify-center text-2xl mb-3">🔑</div>
            <h1 class="text-2xl font-bold text-slate-800">Reset Password</h1>
            <p class="text-slate-500 text-sm mb-6">Enter your email and we'll send you a reset code.</p>
            <form method="POST">
                @csrf
                <div class="mb-5 text-left">
                    <label class="label">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="input" required autofocus>
                </div>
                <button class="btn btn-primary w-full">Send Reset Code</button>
            </form>
            <p class="text-sm text-slate-400 mt-5"><a href="{{ route('login') }}" class="text-blue-600 hover:underline">← Back to login</a></p>
        </div>
    </div>
</div>
@endsection
