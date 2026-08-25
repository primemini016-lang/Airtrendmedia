@extends('layouts.install')

@section('title', 'Database Setup')

@section('content')
<h2 class="text-xl font-bold text-slate-800 mb-1">Database Configuration</h2>
<p class="text-slate-500 text-sm mb-5">Enter your MySQL database credentials. This will write the <code>.env</code> file and run migrations.</p>

<form action="{{ route('install.database') }}" method="POST">
    @csrf
    <div class="grid sm:grid-cols-2 gap-4 mb-4">
        <div><label class="label">Database Host <span class="text-red-500">*</span></label><input type="text" name="db_host" class="input" value="{{ old('db_host', '127.0.0.1') }}" required></div>
        <div><label class="label">Port <span class="text-red-500">*</span></label><input type="number" name="db_port" class="input" value="{{ old('db_port', '3306') }}" required></div>
    </div>
    <div class="mb-4"><label class="label">Database Name <span class="text-red-500">*</span></label><input type="text" name="db_database" class="input" value="{{ old('db_database') }}" placeholder="miniworkers" required></div>
    <div class="grid sm:grid-cols-2 gap-4 mb-4">
        <div><label class="label">Username <span class="text-red-500">*</span></label><input type="text" name="db_username" class="input" value="{{ old('db_username') }}" required></div>
        <div><label class="label">Password</label><input type="password" name="db_password" class="input" value="{{ old('db_password') }}"></div>
    </div>
    <div class="border-t border-slate-100 pt-4 mt-4">
        <div class="grid sm:grid-cols-2 gap-4 mb-4">
            <div><label class="label">App Name <span class="text-red-500">*</span></label><input type="text" name="app_name" class="input" value="{{ old('app_name', 'MiniWorkers') }}" required></div>
            <div><label class="label">App URL <span class="text-red-500">*</span></label><input type="url" name="app_url" class="input" value="{{ old('app_url', request()->getSchemeAndHttpHost()) }}" required></div>
        </div>
    </div>

    <div class="bg-amber-50 rounded-lg p-3 text-xs text-amber-700 mb-4">⚠ This will run <code>migrate:fresh --seed</code>, which creates all tables and default data. Make sure your database is empty or you don't mind losing existing data.</div>

    <div class="flex gap-3">
        <a href="{{ route('install.requirements') }}" class="btn btn-outline flex-1">← Back</a>
        <button type="submit" class="btn btn-primary flex-1">Connect & Migrate →</button>
    </div>
</form>
@endsection
