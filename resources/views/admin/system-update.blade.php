@extends('layouts.admin')

@section('title', 'System Update & AI Auto-Correction')
@section('heading', 'Automatic System Update & AI Self-Healing')

@section('content')
<div class="mb-6">
    <p class="text-slate-500 text-sm">The inbuilt AI auto-correction system runs automatically whenever the website is accessed and silently fixes common deployment issues (missing keys, broken storage permissions, dead symlinks, stale cache). You can also trigger a GitHub code update from here.</p>
</div>

@php
    $diagReport = session('diagnosticsReport', []);
@endphp
@if(! empty($diagReport))

    <div class="card mb-6"><div class="card-body"><h3 class="font-bold mb-2">Private GitHub repository access</h3><p class="text-xs text-slate-500 mb-3">This repository is private. Store a GitHub token here so the automatic updater can securely fetch the selected branch.</p><form action="{{ route('admin.system-update.github-token') }}" method="POST" class="flex flex-col sm:flex-row gap-2">@csrf<input type="password" name="github_token" class="input flex-1" placeholder="github_pat_…"><button class="btn btn-outline">Save Token</button></form></div></div>
<div class="card mb-6">
    <div class="card-body">
        <h3 class="font-bold text-slate-800 mb-3 flex items-center gap-2"><x-icon name="refresh" class="w-5 h-5 text-blue-600" /> Latest Diagnostics Report</h3>
        <pre class="bg-slate-900 text-green-400 p-4 rounded-lg text-xs font-mono overflow-x-auto max-h-72 whitespace-pre-wrap">@foreach($diagReport as $line){{ $line }}
@endforeach</pre>
    </div>
</div>
@endif

<div class="grid lg:grid-cols-3 gap-6 mb-6">
    {{-- AI Auto-Correction status panel --}}
    <div class="card lg:col-span-1">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 mb-1 flex items-center gap-2"><x-icon name="refresh" class="w-5 h-5 text-emerald-600" /> AI Auto-Correction</h3>
            <p class="text-xs text-slate-400 mb-4">Self-healing runs automatically every 5 minutes on web traffic.</p>

            <div class="space-y-2 text-sm">
                @php
                    $checks = [
                        'app_key'        => 'APP Key',
                        'jwt_secret'     => 'JWT Secret',
                        'env_file'       => '.env File',
                        'installed'      => 'Installer Done',
                        'db_connected'   => 'Database',
                        'storage_writable'      => 'Storage Writable',
                        'bootstrap_cache_writable' => 'Bootstrap Cache',
                        'storage_link'   => 'Storage Symlink',
                        'logs_writable'  => 'Logs Writable',
                    ];
                @endphp
                @foreach($checks as $key => $label)
                    @php $ok = $healthStatus[$key] ?? false; @endphp
                    <div class="flex items-center justify-between">
                        <span class="text-slate-600 flex items-center gap-2"><x-icon name="{{ $ok ? 'check' : 'alert-triangle' }}" class="w-4 h-4 {{ $ok ? 'text-emerald-500' : 'text-amber-500' }}" /> {{ $label }}</span>
                        <span class="text-xs font-semibold {{ $ok ? 'text-emerald-600' : 'text-amber-600' }}">{{ $ok ? 'OK' : 'Check' }}</span>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 p-3 rounded-lg {{ ($healthStatus['_overall'] ?? false) ? 'bg-emerald-50' : 'bg-amber-50' }}">
                <p class="text-xs font-semibold {{ ($healthStatus['_overall'] ?? false) ? 'text-emerald-700' : 'text-amber-700' }}">
                    Overall: {{ $healthStatus['_pass_count'] ?? 0 }} / {{ $healthStatus['_total'] ?? 0 }} checks passed
                </p>
            </div>

            <form action="{{ route('admin.system-update.diagnostics') }}" method="POST" class="mt-4" onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').textContent='Running AI sweep...';">
                @csrf
                <button class="btn btn-primary w-full"><x-icon name="refresh" class="w-4 h-4 inline" /> Run AI Diagnostics Now</button>
            </form>
        </div>
    </div>

    {{-- Update info panel --}}
    <div class="card lg:col-span-1">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 mb-4">Update Information</h3>
            <div class="space-y-3 text-sm">
                <div><span class="text-slate-400">Current Version:</span><br><span class="font-bold text-blue-600 text-lg">{{ $currentVersion }}</span></div>
                <div><span class="text-slate-400">Repository:</span><br><a href="{{ $repoUrl }}" target="_blank" class="text-blue-600 hover:underline break-all">{{ $repoUrl }}</a></div>
                <div><span class="text-slate-400">Branch:</span><br><span class="font-semibold">{{ $branch }}</span></div>
                @if($lastUpdate)
                <div><span class="text-slate-400">Last Update:</span><br><span class="font-semibold">{{ \Carbon\Carbon::parse($lastUpdate)->format('M j, Y g:i A') }}</span></div>
                @endif
            </div>

            <hr class="my-5 border-slate-200">

            <h4 class="font-bold text-slate-800 mb-3">Run GitHub Update</h4>
            <form action="{{ route('admin.system-update.run') }}" method="POST" onsubmit="return confirm('This will download and apply the latest code from GitHub. Continue?')">
                @csrf
                <div class="mb-3">
                    <label class="label">Branch</label>
                    <input type="text" name="branch" class="input" value="{{ $branch }}" placeholder="main">
                </div>
                <button class="btn btn-primary w-full" id="update-btn"><x-icon name="refresh" class="w-4 h-4 inline" /> Download &amp; Apply Update</button>
                <p class="text-xs text-amber-600 mt-2"><x-icon name="alert-triangle" class="w-4 h-4 inline" /> This process may take several minutes. Do not navigate away.</p>
            </form>
        </div>
    </div>

    {{-- Recent auto-fix report --}}
    <div class="card lg:col-span-1">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2"><x-icon name="refresh" class="w-5 h-5 text-blue-600" /> Recent Auto-Fix Log</h3>
            @if(! empty($healthReport))
                <pre class="bg-slate-900 text-green-400 p-4 rounded-lg text-xs font-mono overflow-x-auto max-h-72 whitespace-pre-wrap">@foreach($healthReport as $line){{ $line }}
@endforeach</pre>
            @else
                <div class="text-center py-10">
                    <x-icon name="refresh" class="w-10 h-10 mx-auto text-slate-300 mb-2" />
                    <p class="text-slate-400 text-sm">No auto-fixes logged yet.</p>
                    <p class="text-slate-400 text-xs mt-1">The system is healthy or no traffic has triggered a sweep.</p>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Full update log --}}
<div class="card">
    <div class="card-body">
        <h3 class="font-bold text-slate-800 mb-4">GitHub Update Log</h3>
        @if($updateLog)
            <pre class="bg-slate-900 text-green-400 p-4 rounded-lg text-xs font-mono overflow-x-auto max-h-96 whitespace-pre-wrap">{{ $updateLog }}</pre>
        @else
            <p class="text-slate-400 text-sm py-8 text-center">No GitHub updates have been run yet.</p>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.getElementById('update-btn')?.addEventListener('click', function() {
    this.disabled = true;
    this.textContent = 'Updating... Please wait';
});
</script>
@endpush
@endsection
