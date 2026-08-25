@extends('layouts.install')

@section('title', 'Admin Login')

@section('content')
<div class="w-full max-w-md">
    <div class="text-center mb-6">
        <div class="w-14 h-14 mx-auto rounded-xl auth-gradient flex items-center justify-center text-white font-bold text-2xl mb-3">M</div>
        <h1 class="text-2xl font-bold text-slate-800">Admin Panel</h1>
        <p class="text-slate-500 text-sm mt-1">Sign in to manage your marketplace</p>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.login') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="label">Email or Username</label>
                    <input type="text" name="email" class="input" value="{{ old('email') }}" placeholder="admin@example.com" required autofocus>
                </div>
                <div class="mb-4">
                    <label class="label">Password</label>
                    <input type="password" name="password" class="input" placeholder="••••••••" required>
                </div>
                <div class="flex items-center mb-4">
                    <input type="checkbox" name="remember" id="remember" class="rounded border-slate-300 text-blue-600 mr-2">
                    <label for="remember" class="text-sm text-slate-600">Remember me</label>
                </div>
                <button type="submit" class="btn btn-primary w-full">Sign In</button>
            </form>
        </div>
    </div>
    <p class="text-center text-slate-400 text-sm mt-4"><a href="{{ route('home') }}" class="hover:text-blue-600"><x-icon name="arrow-left" class="w-4 h-4 inline" /> Back to site</a></p>
</div>
@endsection
