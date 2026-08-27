@extends('layouts.user')

@section('title', 'Create PTC Ad')
@section('heading', 'Create a PTC Ad Campaign')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="card mb-5 bg-blue-50 border-blue-200">
        <div class="card-body text-sm text-slate-700">
            <p class="font-semibold text-blue-700 mb-1 flex items-center gap-1"><x-icon name="ads" class="w-4 h-4" /> How PTC ads work</p>
            <p>You pay per view. Workers watch your ad for the full duration you set and click "Confirm Execution" to earn a reward. Minimum duration is {{ $minDuration }} seconds, maximum 5 hours. Each 10 seconds = $0.005 reward to the worker. Minimum cost per view is ${{ number_format($minCost, 4) }}. The total cost (cost per view × max views) is deducted from your balance upfront. Every ad is reviewed by an admin before going live.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('user.ptc.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-4">
                    <label class="label">Ad Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" class="input" value="{{ old('title') }}" placeholder="e.g. Check out my new online store" required maxlength="120">
                </div>

                <div class="mb-4">
                    <label class="label">Target URL (where viewers go)</label>
                    <input type="url" name="url" class="input" value="{{ old('url') }}" placeholder="https://your-site.com" maxlength="500">
                </div>

                <div class="mb-4">
                    <label class="label">Description</label>
                    <textarea name="description" class="input" rows="4" placeholder="Describe your offer to viewers…" maxlength="5000">{{ old('description') }}</textarea>
                </div>

                <div class="mb-4">
                    <label class="label">Ad Image (optional)</label>
                    <input type="file" name="image" class="input" accept="image/jpeg,image/png,image/gif,image/webp,image/bmp,image/svg+xml">
                    <p class="text-xs text-slate-400 mt-1">JPG, PNG, GIF, WEBP, BMP, SVG — max 5MB.</p>
                </div>

                <div class="grid sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="label">Duration (seconds) <span class="text-red-500">*</span></label>
                        <input type="number" name="duration_seconds" class="input" value="{{ old('duration_seconds', 10) }}" min="{{ $minDuration }}" max="{{ $maxDuration }}" step="1" required>
                        <p class="text-xs text-slate-400 mt-1">Min {{ $minDuration }}s, max 18000s (5h). Reward = $0.005 per 10s.</p>
                    </div>
                    <div>
                        <label class="label">Cost per View (USD) <span class="text-red-500">*</span></label>
                        <input type="number" name="cost_per_view" class="input" value="{{ old('cost_per_view', number_format($minCost, 4)) }}" min="{{ number_format($minCost, 4) }}" step="0.0001" required>
                        <p class="text-xs text-slate-400 mt-1">Min ${{ number_format($minCost, 4) }}. Must cover the worker reward.</p>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="label">Max Views <span class="text-red-500">*</span></label>
                        <input type="number" name="max_views" class="input" value="{{ old('max_views', 100) }}" min="1" max="1000000" step="1" required>
                    </div>
                    <div>
                        <label class="label">Mode</label>
                        <select name="mode" class="input">
                            <option value="automatic" @selected(old('mode')==='automatic')>Automatic (instant confirm after timer)</option>
                            <option value="manual" @selected(old('mode')==='manual')>Manual (admin reviews each view)</option>
                        </select>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="label">Starts At (optional)</label>
                        <input type="datetime-local" name="starts_at" class="input" value="{{ old('starts_at') }}">
                    </div>
                    <div>
                        <label class="label">Ends At (optional)</label>
                        <input type="datetime-local" name="ends_at" class="input" value="{{ old('ends_at') }}">
                    </div>
                </div>

                <div class="card bg-slate-50 mb-4">
                    <div class="card-body text-sm">
                        <div class="flex justify-between mb-1"><span class="text-slate-500">Estimated reward per view (to worker):</span><span class="font-semibold" id="est-reward">$0.0050</span></div>
                        <div class="flex justify-between mb-1"><span class="text-slate-500">Total campaign cost:</span><span class="font-semibold text-blue-700" id="est-total">$0.50</span></div>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button class="btn btn-primary">Submit for Approval</button>
                    <a href="{{ route('user.ptc.index') }}" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
const dur = document.querySelector('input[name="duration_seconds"]');
const cpv = document.querySelector('input[name="cost_per_view"]');
const maxv = document.querySelector('input[name="max_views"]');
function calc() {
    const d = parseInt(dur.value) || 0;
    const units = Math.floor(d / 10);
    const reward = (units * 0.005).toFixed(4);
    const cost = parseFloat(cpv.value) || 0;
    const views = parseInt(maxv.value) || 0;
    document.getElementById('est-reward').textContent = '$' + reward;
    document.getElementById('est-total').textContent = '$' + (cost * views).toFixed(2);
}
[dur, cpv, maxv].forEach(el => el.addEventListener('input', calc));
calc();
</script>
@endpush
@endsection
