@extends('layouts.app')
@section('title', 'Verify Email — Airtrendmedia')

@section('content')
<div class="max-w-md mx-auto px-4 py-12">
    <div class="card">
        <div class="card-body text-center">
            <div class="inline-flex w-14 h-14 rounded-2xl bg-blue-50 items-center justify-center text-2xl mb-3"><x-icon name="email" class="w-4 h-4 inline" /></div>
            <h1 class="text-2xl font-bold text-slate-800">Verify Your Email</h1>
            <p class="text-slate-500 text-sm mb-6">We sent a 6-digit code to <strong>{{ $email }}</strong>. Enter it below.</p>
            <form method="POST">
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">
                <div class="mb-5">
                    <input type="text" name="code" maxlength="6" class="input text-center text-2xl tracking-[0.5em] font-bold" placeholder="000000" required autofocus>
                </div>
                <button class="btn btn-primary w-full">Verify</button>
            </form>
            <p class="text-sm text-slate-400 mt-5">Didn't get the code? Check your spam folder, or <a href="{{ route('login') }}" class="text-blue-600 hover:underline">try logging in again</a> to resend.</p>
        </div>
    </div>
</div>
@endsection
