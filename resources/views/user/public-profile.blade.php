@extends('layouts.app')
@section('title', $user->name . ' — Profile')
@section('content')
<div class="min-h-screen bg-slate-100">
<div class="max-w-5xl mx-auto">
<div class="bg-white shadow-sm overflow-hidden sm:rounded-b-2xl">
<div class="h-48 sm:h-64 bg-gradient-to-r from-blue-700 via-blue-600 to-indigo-700 relative">
@if($user->cover_image)<img src="{{ storage_asset($user->cover_image) }}" class="w-full h-full object-cover" alt="">@endif
</div>
<div class="px-5 sm:px-8 pb-7">
<div class="flex flex-col sm:flex-row sm:items-end gap-4 -mt-16 relative">
<img src="{{ $user->avatarUrl() }}" class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-lg bg-white" alt="{{ $user->name }}">
<div class="flex-1 pt-2 sm:pt-0"><h1 class="text-3xl font-bold text-slate-900">{{ $user->name }} @if($user->isBlueVerified()) <x-verified-badge size="w-6 h-6" class="ml-1" />@endif</h1><div class="text-slate-500">{{ '@' . $user->username }} @if($user->isOnline())<span class="text-emerald-600 font-semibold">● Active</span>@endif</div></div>
@auth('web') @if(auth('web')->id()!==$user->id)<button id="followBtn" type="button" class="btn btn-primary" data-url="{{ route('user.follow.toggle',$user) }}">{{ auth('web')->user()->following()->where('users.id',$user->id)->exists() ? 'Following' : 'Follow' }}</button><a href="{{ route('messenger.chat.start',$user) }}" class="btn btn-outline">Message</a>@endif @endauth
</div>
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-6">
<a href="{{ route('user.follow.list',[$user->username,'followers']) }}" class="bg-slate-50 rounded-xl p-4 text-center hover:bg-blue-50"><div id="followersCount" class="font-bold text-2xl">{{ number_format($user->followers_count) }}</div><div class="text-slate-500">Followers</div></a>
<a href="{{ route('user.follow.list',[$user->username,'following']) }}" class="bg-slate-50 rounded-xl p-4 text-center hover:bg-blue-50"><div class="font-bold text-2xl">{{ number_format($user->following_count) }}</div><div class="text-slate-500">Following</div></a>
<div class="bg-slate-50 rounded-xl p-4 text-center"><div class="font-bold text-2xl">{{ number_format($user->posts_count) }}</div><div class="text-slate-500">Posts</div></div>
<div class="bg-slate-50 rounded-xl p-4 text-center"><div class="font-bold text-2xl text-amber-500">{{ number_format((float)$user->stars,1) }}</div><div class="text-slate-500">Rating</div></div>
</div>
@if($user->bio)<p class="mt-6 text-slate-700 whitespace-pre-wrap">{{ $user->bio }}</p>@endif
</div></div>
</div>
</div>
@auth('web')
@push('scripts')<script>document.getElementById('followBtn')?.addEventListener('click',function(){const b=this;fetch(b.dataset.url,{method:'POST',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}}).then(r=>r.json()).then(d=>{if(d.following!==undefined){b.textContent=d.following?'Following':'Follow';if(d.followers_count!==undefined)document.getElementById('followersCount').textContent=d.followers_count;}})});</script>@endpush
@endauth
@endsection
