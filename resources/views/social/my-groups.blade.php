@extends('layouts.social')

@section('title', 'My Groups')

@section('main_class', 'max-w-[900px] mx-auto px-4 py-4')

@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-bold">My Groups</h1>
    <a href="{{ route('social.groups.create') }}" class="fb-btn-primary px-4 py-2 rounded-lg font-medium flex items-center gap-2"><x-icon name="create" class="w-5 h-5" /> Create Group</a>
</div>

@if($ownedGroups->isNotEmpty())
<h2 class="font-bold text-lg mb-3">Groups You Admin</h2>
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 mb-6">
    @foreach($ownedGroups as $group)
        <div class="fb-card overflow-hidden">
            <a href="{{ route('social.group.show', $group) }}">
                <img src="{{ $group->profile_image ? Storage::url($group->profile_image) : asset('images/default-group.png') }}" class="w-full h-32 object-cover" alt="{{ $group->name }}">
            </a>
            <div class="p-3">
                <a href="{{ route('social.group.show', $group) }}" class="font-semibold text-sm truncate block">{{ $group->name }}</a>
                <div class="text-xs fb-text-secondary mb-2">{{ $group->members()->where('status','approved')->count() }} members</div>
                <a href="{{ route('social.group.edit', $group) }}" class="block w-full fb-btn-secondary py-1 rounded-md text-sm font-medium text-center">Manage</a>
            </div>
        </div>
    @endforeach
</div>
@endif

@if($joinedGroups->isNotEmpty())
<h2 class="font-bold text-lg mb-3">Groups You've Joined</h2>
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 mb-6">
    @foreach($joinedGroups as $membership)
        <a href="{{ route('social.group.show', $membership->group) }}" class="fb-card overflow-hidden">
            <img src="{{ $membership->group->profile_image ? Storage::url($membership->group->profile_image) : asset('images/default-group.png') }}" class="w-full h-32 object-cover" alt="{{ $membership->group->name }}">
            <div class="p-3">
                <div class="font-semibold text-sm truncate">{{ $membership->group->name }}</div>
                <div class="text-xs fb-text-secondary">{{ $membership->group->members()->where('status','approved')->count() }} members</div>
            </div>
        </a>
    @endforeach
</div>
@endif

@if($pendingRequests->isNotEmpty())
<h2 class="font-bold text-lg mb-3">Pending Requests</h2>
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
    @foreach($pendingRequests as $membership)
        <div class="fb-card overflow-hidden">
            <img src="{{ $membership->group->profile_image ? Storage::url($membership->group->profile_image) : asset('images/default-group.png') }}" class="w-full h-32 object-cover" alt="{{ $membership->group->name }}">
            <div class="p-3">
                <div class="font-semibold text-sm truncate">{{ $membership->group->name }}</div>
                <div class="text-xs fb-text-secondary text-yellow-600">Awaiting approval</div>
            </div>
        </div>
    @endforeach
</div>
@endif

@if($ownedGroups->isEmpty() && $joinedGroups->isEmpty() && $pendingRequests->isEmpty())
<div class="fb-card p-8 text-center">
    <div class="w-16 h-16 rounded-full mx-auto mb-3 flex items-center justify-center" style="background: var(--fb-hover);"><x-icon name="groups" class="w-8 h-8" /></div>
    <h3 class="font-bold text-lg">No groups yet</h3>
    <p class="fb-text-secondary text-sm mb-4">Join or create a group to get started.</p>
    <a href="{{ route('social.groups') }}" class="inline-block fb-btn-primary px-6 py-2 rounded-lg font-medium">Discover Groups</a>
</div>
@endif
@endsection
