@extends('layouts.user')

@section('title', $listing->exists ? 'Edit Listing' : 'Create Listing')
@section('heading', $listing->exists ? 'Edit Your Listing' : 'Create a Marketplace Listing')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="card mb-5 bg-emerald-50 border-emerald-200">
        <div class="card-body text-sm text-slate-700">
            <p class="font-semibold text-emerald-700 mb-1 flex items-center gap-1"><x-icon name="marketplace" class="w-4 h-4" /> How the marketplace works</p>
            <p>List anything you want to sell — a digital product, a physical item, a service, or an account/profile. Buyers contact you through the marketplace. Every new listing is reviewed by an admin before it appears publicly — there is no charge to list.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ $listing->exists ? route('user.marketplace.update', $listing) : route('user.marketplace.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if($listing->exists) @method('PUT') @endif

                <div class="mb-4">
                    <label class="label">Listing Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" class="input" value="{{ old('title', $listing->title ?? '') }}" placeholder="e.g. Premium Canva Pro account (1 year)" required maxlength="191">
                </div>

                <div class="grid sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="label">Category</label>
                        <select name="category_id" class="input">
                            <option value="">Select category…</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $listing->category_id ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label">Listing Type <span class="text-red-500">*</span></label>
                        <select name="listing_type" class="input" required>
                            <option value="">Select type…</option>
                            @foreach($types as $value => $label)
                                <option value="{{ $value }}" {{ old('listing_type', $listing->listing_type ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="label">Price ($) <span class="text-red-500">*</span></label>
                        <input type="number" name="price" class="input" value="{{ old('price', $listing->price ?? '') }}" placeholder="25.00" min="0.50" max="100000" step="0.01" required>
                    </div>
                    <div>
                        <label class="label">Location (optional)</label>
                        <input type="text" name="location" class="input" value="{{ old('location', $listing->location ?? '') }}" placeholder="e.g. Lagos, Nigeria" maxlength="191">
                    </div>
                </div>

                <div class="mb-4">
                    <div class="flex items-center justify-between gap-2"><label class="label mb-0">Description <span class="text-red-500">*</span></label><button type="button" class="ai-assist-btn" data-ai-target="listing-description">AI write</button></div>
                    <textarea id="listing-description" name="description" class="input" rows="7" required maxlength="10000" placeholder="Describe what you're selling, its condition, delivery method, and any terms.">{{ old('description', $listing->description ?? '') }}</textarea>
                </div>

                <div class="mb-4">
                    <label class="label">Cover Image</label>
                    @if($listing->image ?? null)
                        <div class="mb-2"><img src="{{ storage_asset($listing->image) }}" class="w-32 h-32 object-cover rounded-lg border" alt="Current cover"></div>
                    @endif
                    <input type="file" name="image" class="input" accept="image/jpeg,image/png,image/gif,image/webp,image/bmp">
                    <div class="mt-2 rounded-xl overflow-hidden border border-slate-200 hidden" data-image-upload-preview-wrap><img src="" alt="Selected image preview" data-image-upload-preview class="w-full max-h-64 object-contain bg-slate-50"></div>
                    <p class="text-xs text-slate-400 mt-1" data-image-upload-status>JPG, PNG, GIF, WebP or BMP. Max 4 MB.</p>
                    <p class="text-xs text-slate-400 mt-1">JPG, PNG, or WebP. Max 4MB. Recommended 800×600.</p>
                </div>

                <button type="submit" class="btn btn-primary w-full">{{ $listing->exists ? 'Save Changes' : 'Submit Listing for Approval' }}</button>
            </form>
        </div>
    </div>
</div>
@endsection
