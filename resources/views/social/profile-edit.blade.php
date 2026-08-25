@extends('layouts.social')

@section('title', 'Edit Profile')

@section('main_class', 'max-w-[700px] mx-auto px-4 py-4')

@section('content')
<div class="fb-card p-6">
    <h2 class="text-2xl font-bold mb-1">Edit Profile</h2>
    <p class="fb-text-secondary text-sm mb-6">Update your social profile information</p>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-3 rounded-lg mb-4 text-sm">{{ session('success') }}</div>
    @endif

    <form action="{{ route('social.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label class="block font-semibold text-sm mb-1.5">Name</label>
            <input type="text" name="name" value="{{ $user->name }}" class="fb-input py-2.5" required>
        </div>

        <div>
            <label class="block font-semibold text-sm mb-1.5">Bio</label>
            <textarea name="bio" rows="4" class="fb-input py-2.5 resize-none" placeholder="Tell people about yourself...">{{ $user->bio }}</textarea>
        </div>

        <div>
            <label class="block font-semibold text-sm mb-1.5">Cover Photo</label>
            @if($user->cover_image)
                <img src="{{ Storage::url($user->cover_image) }}" class="w-full h-32 object-cover rounded-lg mb-2" alt="Cover">
            @endif
            <input type="file" name="cover_image" accept="image/*" class="w-full text-sm">
        </div>

        <div>
            <label class="block font-semibold text-sm mb-1.5">Account Privacy</label>
            <select name="account_privacy" class="fb-input py-2.5">
                <option value="public" {{ $user->account_privacy === 'public' ? 'selected' : '' }}>Public — Anyone can see your posts</option>
                <option value="private" {{ $user->account_privacy === 'private' ? 'selected' : '' }}>Private — Only friends can see your posts</option>
            </select>
        </div>

        <div class="flex items-center gap-3 p-3 rounded-lg" style="background: var(--fb-hover);">
            <input type="checkbox" name="monetization_enabled" value="1" id="monetization" {{ $user->monetization_enabled ? 'checked' : '' }} class="w-5 h-5">
            <label for="monetization" class="flex-1">
                <span class="font-semibold text-sm flex items-center gap-1.5"><x-icon name="monetization" class="w-4 h-4 text-green-600" /> Enable Monetization</span>
                <span class="text-xs fb-text-secondary">Allow followers to send you stars and subscribe to your content</span>
            </label>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="fb-btn-primary px-6 py-2.5 rounded-lg font-semibold">Save Changes</button>
            <a href="{{ route('social.profile', $user->username) }}" class="fb-btn-secondary px-6 py-2.5 rounded-lg font-semibold">Cancel</a>
        </div>
    </form>
</div>
@endsection
