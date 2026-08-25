@extends('layouts.install')

@section('title', 'Welcome')

@section('content')
<div class="text-center">
    <h2 class="text-xl font-bold text-slate-800 mb-2">Welcome to the MiniWorkers Installer</h2>
    <p class="text-slate-500 text-sm mb-6">This wizard will guide you through setting up your microjob marketplace. It takes about 5 minutes.</p>

    <div class="grid sm:grid-cols-4 gap-3 text-left mb-6">
        <div class="text-center p-3 rounded-lg bg-slate-50">
            <div class="w-10 h-10 mx-auto rounded-full auth-gradient flex items-center justify-center text-white font-bold mb-2">1</div>
            <p class="text-xs font-semibold text-slate-700">Requirements</p>
        </div>
        <div class="text-center p-3 rounded-lg bg-slate-50">
            <div class="w-10 h-10 mx-auto rounded-full auth-gradient flex items-center justify-center text-white font-bold mb-2">2</div>
            <p class="text-xs font-semibold text-slate-700">Database</p>
        </div>
        <div class="text-center p-3 rounded-lg bg-slate-50">
            <div class="w-10 h-10 mx-auto rounded-full auth-gradient flex items-center justify-center text-white font-bold mb-2">3</div>
            <p class="text-xs font-semibold text-slate-700">App Config</p>
        </div>
        <div class="text-center p-3 rounded-lg bg-slate-50">
            <div class="w-10 h-10 mx-auto rounded-full auth-gradient flex items-center justify-center text-white font-bold mb-2">4</div>
            <p class="text-xs font-semibold text-slate-700">Admin & Done</p>
        </div>
    </div>

    <div class="bg-blue-50 rounded-lg p-4 text-left text-sm text-slate-700 mb-6">
        <p class="font-semibold text-blue-700 mb-1">Before you begin, make sure you have:</p>
        <ul class="list-disc list-inside space-y-1 text-slate-600">
            <li>A MySQL database created on your hosting</li>
            <li>Database host, name, username, and password</li>
            <li>Your Paystack API keys (public & secret) — <a href="https://dashboard.paystack.com/" target="_blank" class="text-blue-600 underline">get them here</a></li>
            <li>PHP 8.3 or higher with required extensions</li>
        </ul>
    </div>

    <a href="{{ route('install.requirements') }}" class="btn btn-primary w-full">Start Installation →</a>
</div>
@endsection
