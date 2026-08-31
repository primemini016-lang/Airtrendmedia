@extends('layouts.user')

@section('title', $gig->exists ? 'Edit Gig' : 'Create Gig')
@section('heading', $gig->exists ? 'Edit Your Gig' : 'Create a New Gig')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="card mb-5 bg-blue-50 border-blue-200">
        <div class="card-body text-sm text-slate-700">
            <p class="font-semibold text-blue-700 mb-1 flex items-center gap-1"><x-icon name="gigs" class="w-4 h-4" /> How gigs work</p>
            <p>Gigs are freelance services you offer to other users (e.g. "I will get you 500 real Instagram followers for $10"). Buyers pay you directly. Every new gig is reviewed by an admin before it goes live — there is no charge to list a gig.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ $gig->exists ? route('user.gigs.update', $gig) : route('user.gigs.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if($gig->exists) @method('PUT') @endif

                <div class="mb-4">
                    <label class="label">Gig Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" class="input" value="{{ old('title', $gig->title ?? '') }}" placeholder="e.g. I will give you 500 real YouTube subscribers" required maxlength="191">
                </div>

                <div class="grid sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="label">Category</label>
                        <select name="category_id" class="input">
                            <option value="">Select category…</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $gig->category_id ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label">Price ($) <span class="text-red-500">*</span></label>
                        <input type="number" name="price" class="input" value="{{ old('price', $gig->price ?? '') }}" placeholder="10.00" min="0.50" max="10000" step="0.01" required>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="label">Social Platform</label>
                        <select name="social_platform" class="input">
                            <option value="">— None / General —</option>
                            @foreach($platforms as $p)
                                <option value="{{ $p }}" {{ old('external_platform', $gig->social_platform ?? '') === $p ? 'selected' : '' }}>{{ $p === 'external_platform' ? 'External platform' : ucfirst(str_replace('-', ' ', $p)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label">Sample / Target URL</label>
                        <input type="url" name="social_url" class="input" value="{{ old('social_url', $gig->social_url ?? '') }}" placeholder="https://…">
                    </div>
                </div>

                <div class="mb-4">
                    <div class="flex items-center justify-between gap-2"><label class="label mb-0">Description <span class="text-red-500">*</span></label><button type="button" class="ai-assist-btn" data-ai-target="gig-description">AI write</button></div>
                    <textarea id="gig-description" name="description" class="input" rows="7" required maxlength="10000" placeholder="Describe exactly what the buyer gets. Be specific about quantities, delivery time, and any requirements.">{{ old('description', $gig->description ?? '') }}</textarea>
                </div>

                <div class="mb-4">
                    <label class="label">Cover Image</label>
                    @if($gig->image ?? null)
                        <div class="mb-2"><img src="{{ storage_asset($gig->image) }}" class="w-32 h-32 object-cover rounded-lg border" alt="Current cover"></div>
                    @endif
                    <input type="file" name="image" class="input" accept="image/jpeg,image/png,image/gif,image/webp,image/bmp">
                    <div class="mt-2 rounded-xl overflow-hidden border border-slate-200 hidden" data-image-upload-preview-wrap><img src="" alt="Selected image preview" data-image-upload-preview class="w-full max-h-64 object-contain bg-slate-50"></div>
                    <p class="text-xs text-slate-400 mt-1" data-image-upload-status>JPG, PNG, GIF, WebP or BMP. Max 4 MB.</p>
                    <p class="text-xs text-slate-400 mt-1">JPG, PNG, or WebP. Max 4MB. Recommended 800×600.</p>
                </div>

                <button type="submit" class="btn btn-primary w-full">{{ $gig->exists ? 'Save Changes' : 'Submit Gig for Approval' }}</button>
            </form>
        </div>
    </div>
</div>
@endsection
