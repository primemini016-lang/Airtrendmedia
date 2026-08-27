@extends('layouts.social')

@section('title', 'Edit — ' . $group->name)

@section('main_class', 'max-w-[700px] mx-auto px-4 py-4')

@section('content')
<div class="fb-card p-6">
    <h1 class="text-2xl font-bold mb-1">Edit Group</h1>
    <p class="fb-text-secondary text-sm mb-6">{{ $group->name }}</p>

    <form action="{{ route('social.group.update', $group) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
        @csrf @method('PUT')
        <div><label class="block font-semibold text-sm mb-1.5">Group Name</label><input type="text" name="name" value="{{ $group->name }}" class="fb-input py-2.5" required></div>
        <div><label class="block font-semibold text-sm mb-1.5">Category</label><input type="text" name="category" value="{{ $group->category }}" class="fb-input py-2.5"></div>
        <div><label class="block font-semibold text-sm mb-1.5">Description</label><textarea name="description" rows="4" class="fb-input py-2.5 resize-none">{{ $group->description }}</textarea></div>
        <div>
            <label class="block font-semibold text-sm mb-1.5">Profile Image</label>
            @if($group->profile_image)<img src="{{ Storage::url($group->profile_image) }}" class="w-24 h-24 rounded-lg object-cover mb-2" alt="">@endif
            <input type="file" name="profile_image" accept="image/*" class="w-full text-sm">
        </div>
        <div>
            <label class="block font-semibold text-sm mb-1.5">Cover Image</label>
            @if($group->cover_image)<img src="{{ Storage::url($group->cover_image) }}" class="w-full h-32 object-cover rounded-lg mb-2" alt="">@endif
            <input type="file" name="cover_image" accept="image/*" class="w-full text-sm">
        </div>
        <div>
            <label class="block font-semibold text-sm mb-1.5">Privacy</label>
            <select name="privacy" class="fb-input py-2.5">
                <option value="public" {{ $group->privacy === 'public' ? 'selected' : '' }}>Public</option>
                <option value="private" {{ $group->privacy === 'private' ? 'selected' : '' }}>Private</option>
            </select>
        </div>
        <div class="flex items-center gap-3 p-3 rounded-lg" style="background: var(--fb-hover);">
            <input type="checkbox" name="requires_approval" value="1" {{ $group->requires_approval ? 'checked' : '' }} id="approval" class="w-5 h-5">
            <label for="approval"><span class="font-semibold text-sm">Require admin approval</span></label>
        </div>
        <div class="flex gap-3">
            <button type="submit" class="fb-btn-primary px-6 py-2.5 rounded-lg font-semibold">Save Changes</button>
            <a href="{{ route('social.group.show', $group) }}" class="fb-btn-secondary px-6 py-2.5 rounded-lg font-semibold">Cancel</a>
            <form action="{{ route('social.group.destroy', $group) }}" method="POST" onsubmit="return confirm('Delete this group permanently?')" class="ml-auto">
                @csrf @method('DELETE')
                <button type="submit" class="text-red-600 px-4 py-2.5 rounded-lg font-semibold hover:bg-red-50">Delete Group</button>
            </form>
        </div>
    </form>
</div>
@endsection
