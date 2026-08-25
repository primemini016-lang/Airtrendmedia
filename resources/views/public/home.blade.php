@extends('layouts.app')
@section('title', 'Home — Microjob Marketplace')

@section('content')
<!-- Hero -->
<section class="auth-gradient text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-16 sm:py-24 text-center">
        <h1 class="text-3xl sm:text-5xl font-extrabold leading-tight">Complete Small Tasks & Earn Money Online</h1>
        <p class="mt-4 text-lg text-blue-100 max-w-2xl mx-auto">{{ $s->footer_text ?? 'Join thousands of workers earning by completing microtasks, or post jobs and get them done by a global workforce.' }}</p>
        <div class="mt-8 flex items-center justify-center gap-3 flex-wrap">
            @guest
            <a href="{{ route('register') }}" class="btn bg-white text-blue-700 hover:bg-blue-50">Get Started — Free</a>
            <a href="{{ route('browse') }}" class="btn btn-outline text-white border-white">Browse Tasks</a>
            @else
            <a href="{{ route('user.dashboard') }}" class="btn bg-white text-blue-700 hover:bg-blue-50">Go to Dashboard</a>
            <a href="{{ route('browse') }}" class="btn btn-outline text-white border-white">Browse Tasks</a>
            @endguest
        </div>
        <div class="mt-10 grid grid-cols-3 gap-4 max-w-lg mx-auto text-center">
            <div><div class="text-2xl font-bold">{{ \App\Models\Task::where('status',1)->count() }}</div><div class="text-xs text-blue-100">Active Tasks</div></div>
            <div><div class="text-2xl font-bold">{{ \App\Models\User::count() }}</div><div class="text-xs text-blue-100">Members</div></div>
            <div><div class="text-2xl font-bold">${{ number_format((float)\App\Models\Transaction::whereIn('type',['task_credit','affiliate_reward'])->sum('amount'),0) }}</div><div class="text-xs text-blue-100">Paid Out</div></div>
        </div>
    </div>
</section>

<!-- How it works -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 py-14">
    <h2 class="text-2xl font-bold text-center text-slate-800 mb-2">How It Works</h2>
    <p class="text-center text-slate-500 mb-10">Three simple steps to start earning</p>
    <div class="grid md:grid-cols-3 gap-6">
        <div class="card text-center p-6">
            <div class="w-14 h-14 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-2xl mx-auto mb-4">1</div>
            <h3 class="font-bold text-slate-800 mb-2">Register & Activate</h3>
            <p class="text-sm text-slate-500">Create your free account and pay the one-time $5 activation fee to unlock the marketplace.</p>
        </div>
        <div class="card text-center p-6">
            <div class="w-14 h-14 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-2xl mx-auto mb-4">2</div>
            <h3 class="font-bold text-slate-800 mb-2">Complete Tasks</h3>
            <p class="text-sm text-slate-500">Browse available microjobs, book a slot, complete the work, and submit your proof.</p>
        </div>
        <div class="card text-center p-6">
            <div class="w-14 h-14 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-2xl mx-auto mb-4">3</div>
            <h3 class="font-bold text-slate-800 mb-2">Get Paid</h3>
            <p class="text-sm text-slate-500">Once the employer approves your proof, the funds are credited to your wallet instantly. Withdraw anytime.</p>
        </div>
    </div>
</section>

<!-- Categories -->
@if($categories->isNotEmpty())
<section class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
    <h2 class="text-2xl font-bold text-slate-800 mb-6">Browse by Category</h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach($categories as $cat)
        <a href="{{ route('browse', ['category'=>$cat->id]) }}" class="card p-5 hover:shadow-md transition text-center group">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-xl mx-auto mb-3 group-hover:scale-110 transition" style="background:{{ $cat->color ?? '#2563eb' }}20;color:{{ $cat->color ?? '#2563eb' }}">@categoryIcon($cat->icon)</div>
            <p class="font-semibold text-slate-800 text-sm">{{ $cat->name }}</p>
        </a>
        @endforeach
    </div>
</section>
@endif

<!-- Live tasks -->
@if($liveTasks->isNotEmpty())
<section class="max-w-7xl mx-auto px-4 sm:px-6 py-10">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Latest Tasks</h2>
        <a href="{{ route('browse') }}" class="text-blue-600 text-sm font-semibold hover:underline">View all <x-icon name="arrow-right" class="w-4 h-4 inline" /></a>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($liveTasks as $task)
        <div class="card p-5 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-2">
                <span class="badge badge-info inline-flex items-center gap-1">@if($task->category)<span>@categoryIcon($task->category->icon)</span>@endif {{ $task->category?->name ?? 'General' }}</span>
                <span class="text-xs text-slate-400">{{ $task->booked }}/{{ $task->amount }} slots</span>
            </div>
            <h3 class="font-semibold text-slate-800 mb-1 line-clamp-2">{{ $task->title }}</h3>
            <p class="text-sm text-slate-500 line-clamp-2 mb-3">{{ $task->details }}</p>
            <div class="flex items-center justify-between">
                <span class="text-blue-600 font-bold">${{ number_format((float)$task->price,2) }}</span>
                @guest<a href="{{ route('register') }}" class="btn btn-primary text-xs">Sign up to view</a>@else<a href="{{ route('user.task',$task) }}" class="btn btn-primary text-xs">View Task</a>@endguest
            </div>
        </div>
        @endforeach
    </div>
</section>
@endif

<!-- CTA -->
<section class="bg-slate-900 text-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-14 text-center">
        <h2 class="text-2xl sm:text-3xl font-bold mb-3">Ready to Start Earning?</h2>
        <p class="text-slate-400 mb-6">Join the marketplace today and turn your spare time into income.</p>
        @guest<a href="{{ route('register') }}" class="btn btn-primary">Create Free Account</a>@else<a href="{{ route('user.dashboard') }}" class="btn btn-primary">Go to Dashboard</a>@endguest
    </div>
</section>
@endsection
