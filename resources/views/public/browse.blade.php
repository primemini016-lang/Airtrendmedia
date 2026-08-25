@extends('layouts.app')
@section('title', 'Browse Tasks — Microjob Marketplace')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
    <h1 class="text-2xl font-bold text-slate-800 mb-1">Browse Tasks</h1>
    <p class="text-slate-500 mb-6">Find microjobs that match your skills and start earning.</p>

    <!-- Filters -->
    <form method="GET" class="card p-4 mb-6 flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[200px]">
            <label class="label">Search</label>
            <input type="text" name="q" value="{{ request('q') }}" class="input" placeholder="Search task title or description...">
        </div>
        <div class="min-w-[180px]">
            <label class="label">Category</label>
            <select name="category" class="input">
                <option value="">All categories</option>
                @foreach($categories as $cat)<option value="{{ $cat->id }}" @selected(request('category')==$cat->id)>{{ $cat->name }}</option>@endforeach
            </select>
        </div>
        <button class="btn btn-primary">Filter</button>
        <a href="{{ route('browse') }}" class="btn btn-outline">Reset</a>
    </form>

    @if($tasks->isNotEmpty())
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($tasks as $task)
        <div class="card p-5 hover:shadow-md transition flex flex-col">
            <div class="flex items-center justify-between mb-2">
                <span class="badge badge-info inline-flex items-center gap-1">@if($task->category)<span>@categoryIcon($task->category->icon)</span>@endif {{ $task->category?->name ?? 'General' }}</span>
                <span class="text-xs text-slate-400">{{ $task->booked }}/{{ $task->amount }} booked</span>
            </div>
            <h3 class="font-semibold text-slate-800 mb-1 line-clamp-2">{{ $task->title }}</h3>
            <p class="text-sm text-slate-500 line-clamp-3 flex-1 mb-3">{{ $task->details }}</p>
            <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                <div>
                    <span class="text-blue-600 font-bold text-lg">${{ number_format((float)$task->price,2) }}</span>
                    <span class="text-xs text-slate-400">/ task</span>
                </div>
                @guest<a href="{{ route('register') }}" class="btn btn-primary text-xs">Sign Up</a>@else<a href="{{ route('user.task',$task) }}" class="btn btn-primary text-xs">View & Book</a>@endguest
            </div>
        </div>
        @endforeach
    </div>
    <div class="mt-6">{{ $tasks->links() }}</div>
    @else
    <div class="card p-12 text-center text-slate-400">
        <p class="text-4xl mb-3">📭</p>
        <p>No tasks found. Check back soon or try a different search.</p>
    </div>
    @endif
</div>
@endsection
