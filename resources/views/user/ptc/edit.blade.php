@extends('layouts.user')

@section('title', 'Edit PTC Ad')
@section('heading', 'Edit PTC Ad — ' . $ad->title)

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="card mb-5 bg-amber-50 border-amber-200">
        <div class="card-body text-sm text-slate-700">
            <p class="font-semibold text-amber-700 mb-1">Editing sends this ad back for admin approval.</p>
            <p>Cost per view and max views cannot be changed after creation. Only title, URL, description, image, duration, mode and schedule can be edited here.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('user.ptc.update', $ad) }}" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')

                <div class="mb-4">
                    <label class="label">Ad Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" class="input" value="{{ old('title', $ad->title) }}" required maxlength="120">
                </div>

                <div class="mb-4">
                    <label class="label">Target URL</label>
                    <input type="url" name="url" class="input" value="{{ old('url', $ad->url) }}" maxlength="500">
                </div>

                <div class="mb-4">
                    <label class="label">Description</label>
                    <textarea name="description" class="input" rows="4" maxlength="5000">{{ old('description', $ad->description) }}</textarea>
                </div>

                <div class="mb-4">
                    <label class="label">Ad Image</label>
                    @if($ad->image)<img src="{{ $ad->imageUrl() }}" class="w-32 h-20 object-cover rounded mb-2">@endif
                    <input type="file" name="image" class="input" accept="image/jpeg,image/png,image/gif,image/webp,image/bmp,image/svg+xml">
                </div>

                <div class="grid sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="label">Duration (seconds) <span class="text-red-500">*</span></label>
                        <input type="number" name="duration_seconds" class="input" value="{{ old('duration_seconds', $ad->duration_seconds) }}" min="{{ $minDuration }}" max="{{ $maxDuration }}" step="1" required>
                    </div>
                    <div>
                        <label class="label">Mode</label>
                        <select name="mode" class="input">
                            <option value="automatic" @selected(old('mode', $ad->mode)==='automatic')>Automatic</option>
                            <option value="manual" @selected(old('mode', $ad->mode)==='manual')>Manual</option>
                        </select>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="label">Starts At</label>
                        <input type="datetime-local" name="starts_at" class="input" value="{{ old('starts_at', optional($ad->starts_at)->format('Y-m-d\TH:i')) }}">
                    </div>
                    <div>
                        <label class="label">Ends At</label>
                        <input type="datetime-local" name="ends_at" class="input" value="{{ old('ends_at', optional($ad->ends_at)->format('Y-m-d\TH:i')) }}">
                    </div>
                </div>

                <div class="flex gap-3">
                    <button class="btn btn-primary">Update & Resubmit</button>
                    <a href="{{ route('user.ptc.index') }}" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
