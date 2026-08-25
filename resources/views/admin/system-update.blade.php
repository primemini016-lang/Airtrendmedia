@extends('layouts.admin')

@section('title', 'System Update')
@section('heading', 'Automatic System Update')

@section('content')
<div class="mb-6">
    <p class="text-slate-500 text-sm">Trigger an automatic update from the GitHub repository. The system will download the latest code, apply migrations, and clear caches.</p>
</div>

<div class="grid lg:grid-cols-3 gap-6">
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

            <h4 class="font-bold text-slate-800 mb-3">Run Update</h4>
            <form action="{{ route('admin.system-update.run') }}" method="POST" onsubmit="return confirm('This will download and apply the latest code from GitHub. Continue?')">
                @csrf
                <div class="mb-3">
                    <label class="label">Branch</label>
                    <input type="text" name="branch" class="input" value="{{ $branch }}" placeholder="main">
                </div>
                <button class="btn btn-primary w-full" id="update-btn">⬇ Download &amp; Apply Update</button>
                <p class="text-xs text-amber-600 mt-2">⚠ This process may take several minutes. Do not navigate away.</p>
            </form>
        </div>
    </div>

    <div class="card lg:col-span-2">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 mb-4">Update Log</h3>
            @if($updateLog)
                <pre class="bg-slate-900 text-green-400 p-4 rounded-lg text-xs font-mono overflow-x-auto max-h-96 whitespace-pre-wrap">{{ $updateLog }}</pre>
            @else
                <p class="text-slate-400 text-sm py-8 text-center">No updates have been run yet.</p>
            @endif
        </div>
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
