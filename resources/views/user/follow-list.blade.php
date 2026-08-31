@extends('layouts.app')
@section('title', ucfirst($type).' of '.$user->name)
@section('content')
<div class="max-w-3xl mx-auto px-4 py-6">
<a href="{{ route('user.public-profile',$user) }}" class="text-blue-600">← Back to profile</a>
<h1 class="text-2xl font-bold mt-5">{{ ucfirst($type) }} · {{ $user->name }}</h1>
<div class="mt-4 bg-white rounded-2xl shadow-sm p-4 space-y-3">
@forelse($items as $item)
<a href="{{ route('user.public-profile',$item) }}" class="flex items-center gap-3 p-2 rounded-xl hover:bg-slate-50">
<img src="{{ $item->avatarUrl() }}" class="w-12 h-12 rounded-full object-cover" alt=""><div class="flex-1"><div class="font-semibold">{{ $item->name }}</div><div class="text-sm text-slate-500">{{ '@' . $item->username }} @if($item->isOnline())<span class="text-emerald-600">● Active</span>@endif</div></div>
</a>
@empty <p class="text-slate-500 py-8 text-center">No users yet.</p>@endforelse
</div>
<div class="mt-5">{{ $items->links() }}</div></div>
@endsection
