@extends('layouts.admin')
@section('title','Platform Control Center')
@section('heading','Platform Control Center')
@section('content')
<div class="mb-6 rounded-2xl bg-gradient-to-br from-slate-950 via-blue-950 to-blue-700 p-6 text-white shadow-xl"><div class="flex items-start justify-between gap-5 flex-wrap"><div><p class="text-blue-200 text-xs font-bold uppercase tracking-widest">Super Admin · Full Platform Control</p><h2 class="text-2xl sm:text-3xl font-black mt-2">Airtrendmedia Control Center</h2><p class="text-blue-100/80 text-sm mt-2 max-w-2xl">Moderate social content, manage creators, control pages and groups, and reach every major platform subsystem from one place.</p></div><div class="rounded-2xl bg-white/10 p-4"><x-icon name="admin" class="w-10 h-10"/></div></div></div>
<div class="grid grid-cols-2 lg:grid-cols-6 gap-4 mb-6">
@foreach([['posts','Social Posts','social.posts','feed'],['comments','Comments','social.comments','comment'],['stories','Stories','social.stories','stories'],['pages','Pages','social.pages','pages'],['groups','Groups','social.groups','groups'],['users','Users','users','users'],['videos','Videos','social.posts','video'],['blogs','Blog Articles','blog','blog']] as [$key,$label,$route,$icon])
<a href="{{ route('admin.'.$route) }}" class="card hover:-translate-y-0.5 transition"><div class="card-body"><x-icon name="{{ $icon }}" class="w-5 h-5 text-blue-600 mb-3"/><p class="text-2xl font-black">{{ number_format($stats[$key]) }}</p><p class="text-xs text-slate-500 mt-1">{{ $label }}</p></div></a>
@endforeach
</div>
<div class="grid lg:grid-cols-3 gap-4 mb-6">
<a class="card hover:border-blue-300" href="{{ route('admin.users') }}"><div class="card-body"><b>Users & Wallets</b><p class="text-xs text-slate-500 mt-1">Ban, activate, inspect balances, transactions and impersonate users.</p></div></a>
<a class="card hover:border-blue-300" href="{{ route('admin.blog') }}"><div class="card-body"><b>Platform Blog</b><p class="text-xs text-slate-500 mt-1">Create, edit, publish, feature and remove system articles.</p></div></a>
<a class="card hover:border-blue-300" href="{{ route('admin.appearance') }}"><div class="card-body"><b>Appearance & Extension</b><p class="text-xs text-slate-500 mt-1">Control branding, injected HTML, CSS, logos and platform appearance.</p></div></a>
</div>
<div class="card"><div class="card-body"><div class="flex items-center justify-between mb-4"><h3 class="font-bold">Latest Social Activity</h3><a href="{{ route('admin.social.posts') }}" class="text-sm text-blue-600">Manage all</a></div><div class="space-y-3">@foreach($recentPosts as $post)<div class="flex gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800"><div class="w-9 h-9 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold">{{ strtoupper(substr($post->user?->name ?? 'A',0,1)) }}</div><div class="min-w-0 flex-1"><p class="text-sm font-semibold">{{ $post->user?->name ?? 'System' }} <span class="font-normal text-slate-400">posted</span></p><p class="text-sm text-slate-600 dark:text-slate-300 line-clamp-2">{{ $post->content ?: 'Media post' }}</p></div><a href="{{ route('admin.social.posts') }}" class="text-xs text-blue-600">Edit</a></div>@endforeach</div></div></div>
@endsection
