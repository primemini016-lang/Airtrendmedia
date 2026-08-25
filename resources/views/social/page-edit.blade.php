@extends('layouts.social')

@section('title', 'Edit — ' . $page->name)

@section('main_class', 'max-w-[700px] mx-auto px-4 py-4')

@section('content')
<div class="fb-card p-6">
    <h1 class="text-2xl font-bold mb-1">Edit Page</h1>
    <p class="fb-text-secondary text-sm mb-6">{{ $page->name }}</p>

    <form action="{{ route('social.page.update', $page) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
        @csrf
        @method('PUT')
        <div>
            <label class="block font-semibold text-sm mb-1.5">Page Name</label>
            <input type="text" name="name" value="{{ $page->name }}" class="fb-input py-2.5" required>
        </div>
        <div>
            <label class="block font-semibold text-sm mb-1.5">Category</label>
            <input type="text" name="category" value="{{ $page->category }}" class="fb-input py-2.5">
        </div>
        <div>
            <label class="block font-semibold text-sm mb-1.5">Description</label>
            <textarea name="description" rows="4" class="fb-input py-2.5 resize-none">{{ $page->description }}</textarea>
        </div>
        <div>
            <label class="block font-semibold text-sm mb-1.5">Profile Image</label>
            @if($page->profile_image)<img src="{{ Storage::url($page->profile_image) }}" class="w-24 h-24 rounded-lg object-cover mb-2" alt="">@endif
            <input type="file" name="profile_image" accept="image/*" class="w-full text-sm">
        </div>
        <div>
            <label class="block font-semibold text-sm mb-1.5">Cover Image</label>
            @if($page->cover_image)<img src="{{ Storage::url($page->cover_image) }}" class="w-full h-32 object-cover rounded-lg mb-2" alt="">@endif
            <input type="file" name="cover_image" accept="image/*" class="w-full text-sm">
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block font-semibold text-sm mb-1.5">Website</label><input type="url" name="website" value="{{ $page->website }}" class="fb-input py-2.5"></div>
            <div><label class="block font-semibold text-sm mb-1.5">Location</label><input type="text" name="location" value="{{ $page->location }}" class="fb-input py-2.5"></div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block font-semibold text-sm mb-1.5">Phone</label><input type="text" name="phone" value="{{ $page->phone }}" class="fb-input py-2.5"></div>
            <div><label class="block font-semibold text-sm mb-1.5">Email</label><input type="email" name="email" value="{{ $page->email }}" class="fb-input py-2.5"></div>
        </div>
        <div class="flex gap-3">
            <button type="submit" class="fb-btn-primary px-6 py-2.5 rounded-lg font-semibold">Save Changes</button>
            <a href="{{ route('social.page.show', $page) }}" class="fb-btn-secondary px-6 py-2.5 rounded-lg font-semibold">Cancel</a>
            <form action="{{ route('social.page.destroy', $page) }}" method="POST" onsubmit="return confirm('Delete this page permanently?')" class="ml-auto">
                @csrf @method('DELETE')
                <button type="submit" class="text-red-600 px-4 py-2.5 rounded-lg font-semibold hover:bg-red-50">Delete Page</button>
            </form>
        </div>
    </form>
</div>
@endsection
