@extends('layouts.admin')

@section('title', 'PTC Settings')
@section('heading', 'PTC System Settings — God Mode')

@section('content')
<div class="max-w-2xl">
    <a href="{{ route('admin.ptc') }}" class="text-blue-600 hover:underline text-sm mb-4 inline-block">← Back to PTC Management</a>

    @if(session('success'))<div class="alert alert-success mb-4">{{ session('success') }}</div>@endif

    <div class="card mb-5 bg-blue-50 border-blue-200">
        <div class="card-body text-sm text-slate-700">
            <p class="font-semibold text-blue-700 mb-1">PTC Reward Formula</p>
            <p>Reward per view = floor(duration_seconds ÷ seconds_per_unit) × reward_per_unit. Default: each 10 seconds = $0.005. The minimum cost per view is the floor advertisers must pay. Workers earn instantly after the green "Confirm Execution" button.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.ptc.settings') }}" method="POST">
                @csrf
                <div class="grid sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="label">Min Cost per View (USD)</label>
                        <input type="number" step="0.0001" min="0.005" name="ptc_min_cost_per_view" class="input" value="{{ $settings['ptc_min_cost_per_view'] }}" required>
                    </div>
                    <div>
                        <label class="label">Min Duration (seconds)</label>
                        <input type="number" min="10" name="ptc_min_duration" class="input" value="{{ $settings['ptc_min_duration'] }}" required>
                    </div>
                    <div>
                        <label class="label">Max Duration (seconds)</label>
                        <input type="number" min="10" max="18000" name="ptc_max_duration" class="input" value="{{ $settings['ptc_max_duration'] }}" required>
                    </div>
                    <div>
                        <label class="label">Reward per Unit (USD)</label>
                        <input type="number" step="0.0001" min="0.001" name="ptc_reward_per_unit" class="input" value="{{ $settings['ptc_reward_per_unit'] }}" required>
                    </div>
                    <div>
                        <label class="label">Seconds per Unit</label>
                        <input type="number" min="1" name="ptc_seconds_per_unit" class="input" value="{{ $settings['ptc_seconds_per_unit'] }}" required>
                    </div>
                    <div>
                        <label class="label">PTC System Enabled</label>
                        <select name="ptc_enabled" class="input">
                            <option value="1" @selected($settings['ptc_enabled']==='1')>Enabled</option>
                            <option value="0" @selected($settings['ptc_enabled']==='0')>Disabled</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="label">Auto-Approve New Ads</label>
                        <select name="ptc_auto_approve" class="input">
                            <option value="0" @selected($settings['ptc_auto_approve']==='0')>Manual approval (recommended)</option>
                            <option value="1" @selected($settings['ptc_auto_approve']==='1')>Automatic approval</option>
                        </select>
                    </div>
                </div>
                <button class="btn btn-primary">Save Settings</button>
            </form>
        </div>
    </div>
</div>
@endsection
