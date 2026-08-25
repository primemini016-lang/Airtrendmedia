@extends('layouts.social')

@section('title', 'Create Page')

@section('main_class', 'max-w-[700px] mx-auto px-4 py-4')

@section('content')
<div class="fb-card p-6">
    <h1 class="text-2xl font-bold mb-1">Create a Page</h1>
    <p class="fb-text-secondary text-sm mb-6">Pages are for businesses, brands, and public figures to connect with people</p>

    <form action="{{ route('social.pages.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
        @csrf
        <div>
            <label class="block font-semibold text-sm mb-1.5">Page Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" class="fb-input py-2.5" placeholder="e.g. My Awesome Brand" required>
        </div>
        <div>
            <label class="block font-semibold text-sm mb-1.5">Category</label>
            <input type="text" name="category" class="fb-input py-2.5" placeholder="e.g. Technology, Entertainment, Business">
        </div>
        <div>
            <label class="block font-semibold text-sm mb-1.5">Description</label>
            <textarea name="description" rows="4" class="fb-input py-2.5 resize-none" placeholder="Tell people about your page..."></textarea>
        </div>
        <div>
            <label class="block font-semibold text-sm mb-1.5">Profile Image</label>
            <input type="file" name="profile_image" accept="image/*" class="w-full text-sm">
        </div>
        <div>
            <label class="block font-semibold text-sm mb-1.5">Cover Image</label>
            <input type="file" name="cover_image" accept="image/*" class="w-full text-sm">
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold text-sm mb-1.5">Website</label>
                <input type="url" name="website" class="fb-input py-2.5" placeholder="https://...">
            </div>
            <div>
                <label class="block font-semibold text-sm mb-1.5">Location</label>
                <input type="text" name="location" class="fb-input py-2.5" placeholder="City, Country">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold text-sm mb-1.5">Phone</label>
                <input type="text" name="phone" class="fb-input py-2.5" placeholder="+1 234 567 890">
            </div>
            <div>
                <label class="block font-semibold text-sm mb-1.5">Email</label>
                <input type="email" name="email" class="fb-input py-2.5" placeholder="page@example.com">
            </div>
        </div>
        <div class="flex gap-3">
            <button type="submit" class="fb-btn-primary px-6 py-2.5 rounded-lg font-semibold">Create Page</button>
            <a href="{{ route('social.pages') }}" class="fb-btn-secondary px-6 py-2.5 rounded-lg font-semibold">Cancel</a>
        </div>
    </form>
</div>
@endsection
