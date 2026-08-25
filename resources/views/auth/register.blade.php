@extends('layouts.app')
@section('title', 'Create Account — Microjob Marketplace')

@section('content')
<div class="max-w-md mx-auto px-4 py-10">
    <div class="card">
        <div class="card-body">
            <div class="text-center mb-6">
                <div class="inline-flex w-14 h-14 rounded-2xl auth-gradient items-center justify-center text-white text-2xl font-bold mb-3">M</div>
                <h1 class="text-2xl font-bold text-slate-800">Create Your Account</h1>
                <p class="text-slate-500 text-sm">Join the marketplace and start earning</p>
            </div>
            <form method="POST">
                @csrf
                <div class="mb-4">
                    <label class="label">Full Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="input" required>
                </div>
                <div class="mb-4">
                    <label class="label">Username</label>
                    <input type="text" name="username" value="{{ old('username') }}" class="input" required>
                    <p class="text-xs text-slate-400 mt-1">Letters, numbers, dashes, underscores. 4–32 characters.</p>
                </div>
                <div class="mb-4">
                    <label class="label">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="input" required>
                </div>
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div>
                        <label class="label">Phone (optional)</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="input">
                    </div>
                    <div>
                        <label class="label">Country</label>
                        <select name="country_code" class="input">
                            <option value="">Select…</option>
                            @foreach($countries as $c)<option value="{{ $c->code }}" @selected(old('country_code')==$c->code)>{{ $c->name }} ({{ $c->code }})</option>@endforeach
                        </select>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="label">Password</label>
                    <input type="password" name="password" class="input" required>
                </div>
                <div class="mb-4">
                    <label class="label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="input" required>
                </div>
                @if($referrer)<input type="hidden" name="referrer" value="{{ $referrer }}">
                <div class="mb-4 px-3 py-2 rounded-lg bg-blue-50 text-blue-700 text-sm"><x-icon name="check" class="w-4 h-4 inline" /> Referred by <strong>{{ $referrer }}</strong></div>
                @endif
                <button class="btn btn-primary w-full">Create Account</button>
            </form>
            <p class="text-center text-sm text-slate-500 mt-5">Already have an account? <a href="{{ route('login') }}" class="text-blue-600 font-semibold hover:underline">Sign in</a></p>
        </div>
    </div>
</div>
@endsection
