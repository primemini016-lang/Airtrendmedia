@extends('layouts.install')

@section('title', 'Requirements Check')

@section('content')
<h2 class="text-xl font-bold text-slate-800 mb-1">Server Requirements</h2>
<p class="text-slate-500 text-sm mb-5">We're checking that your server meets the minimum requirements.</p>

<div class="space-y-2 mb-5">
    @foreach($checks as $label => $ok)
        <div class="flex items-center justify-between p-3 rounded-lg {{ $ok ? 'bg-green-50' : 'bg-red-50' }}">
            <span class="text-sm font-medium text-slate-700">{{ $label }}</span>
            @if($ok)<span class="badge badge-success">✓ Passed</span>@else<span class="badge badge-danger">✕ Failed</span>@endif
        </div>
    @endforeach
</div>

<div class="flex items-center justify-between text-sm mb-5">
    <span class="text-slate-500">{{ $passed }} / {{ count($checks) }} checks passed</span>
    @if($allPassed)<span class="badge badge-success">All requirements met!</span>@else<span class="badge badge-danger">Some requirements are missing</span>@endif
</div>

<div class="flex gap-3">
    <a href="{{ route('install.start') }}" class="btn btn-outline flex-1">← Back</a>
    @if($allPassed)
        <a href="{{ route('install.database') }}" class="btn btn-primary flex-1">Continue →</a>
    @else
        <button class="btn btn-primary flex-1 opacity-50 cursor-not-allowed" disabled>Fix issues to continue</button>
    @endif
</div>
@endsection
