@extends('layouts.social')

@section('title', 'Create Group')

@section('main_class', 'max-w-[700px] mx-auto px-4 py-4')

@section('content')
<div class="fb-card p-6">
    <h1 class="text-2xl font-bold mb-1">Create a Group</h1>
    <p class="fb-text-secondary text-sm mb-6">Groups are great for connecting with people who share your interests</p>

    <form action="{{ route('social.groups.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
        @csrf
        <div>
            <label class="block font-semibold text-sm mb-1.5">Group Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" class="fb-input py-2.5" placeholder="e.g. Photography Enthusiasts" required>
        </div>
        <div>
            <label class="block font-semibold text-sm mb-1.5">Category</label>
            <input type="text" name="category" class="fb-input py-2.5" placeholder="e.g. Hobbies, Technology, Sports">
        </div>
        <div>
            <label class="block font-semibold text-sm mb-1.5">Description</label>
            <textarea name="description" rows="4" class="fb-input py-2.5 resize-none" placeholder="Describe what your group is about..."></textarea>
        </div>
        <div>
            <label class="block font-semibold text-sm mb-1.5">Profile Image</label>
            <input type="file" name="profile_image" accept="image/*" class="w-full text-sm">
        </div>
        <div>
            <label class="block font-semibold text-sm mb-1.5">Cover Image</label>
            <input type="file" name="cover_image" accept="image/*" class="w-full text-sm">
        </div>
        <div>
            <label class="block font-semibold text-sm mb-1.5">Privacy</label>
            <div class="space-y-2">
                <label class="flex items-center gap-3 p-3 rounded-lg fb-hover-bg cursor-pointer">
                    <input type="radio" name="privacy" value="public" checked class="w-5 h-5">
                    <div><div class="font-medium text-sm flex items-center gap-1.5"><x-icon name="globe" class="w-4 h-4" /> Public</div><div class="text-xs fb-text-secondary">Anyone can see and join the group</div></div>
                </label>
                <label class="flex items-center gap-3 p-3 rounded-lg fb-hover-bg cursor-pointer">
                    <input type="radio" name="privacy" value="private" class="w-5 h-5">
                    <div><div class="font-medium text-sm flex items-center gap-1.5"><x-icon name="lock" class="w-4 h-4" /> Private</div><div class="text-xs fb-text-secondary">Only members can see posts</div></div>
                </label>
            </div>
        </div>
        <div class="flex items-center gap-3 p-3 rounded-lg" style="background: var(--fb-hover);">
            <input type="checkbox" name="requires_approval" value="1" id="approval" class="w-5 h-5">
            <label for="approval"><span class="font-semibold text-sm">Require admin approval to join</span><br><span class="text-xs fb-text-secondary">New members must be approved by an admin</span></label>
        </div>
        <div class="flex gap-3">
            <button type="submit" class="fb-btn-primary px-6 py-2.5 rounded-lg font-semibold">Create Group</button>
            <a href="{{ route('social.groups') }}" class="fb-btn-secondary px-6 py-2.5 rounded-lg font-semibold">Cancel</a>
        </div>
    </form>
</div>
@endsection
