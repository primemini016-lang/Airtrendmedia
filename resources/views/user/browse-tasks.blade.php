@extends('layouts.user')
@section('title', 'Browse Tasks')
@section('heading', 'Browse Tasks')

@section('content')
<form method="GET" class="card p-4 mb-6 flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-[200px]">
        <label class="label">Search</label>
        <input type="text" name="q" value="{{ request('q') }}" class="input" placeholder="Search tasks...">
    </div>
    <div class="min-w-[160px]">
        <label class="label">Category</label>
        <select name="category" class="input">
            <option value="">All</option>
            @foreach($categories as $cat)<option value="{{ $cat->id }}" @selected(request('category')==$cat->id)>{{ $cat->name }}</option>@endforeach
        </select>
    </div>
    <div class="min-w-[140px]">
        <label class="label">Sort</label>
        <select name="sort" class="input">
            <option value="newest" @selected(request('sort')=='newest')>Newest</option>
            <option value="price_high" @selected(request('sort')=='price_high')>Price: High→Low</option>
            <option value="price_low" @selected(request('sort')=='price_low')>Price: Low→High</option>
        </select>
    </div>
    <button class="btn btn-primary">Filter</button>
    <a href="{{ route('user.tasks') }}" class="btn btn-outline">Reset</a>
</form>

@if($tasks->isNotEmpty())
<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($tasks as $task)
    <div class="card p-5 flex flex-col">
        <div class="flex items-center justify-between mb-2">
            <span class="badge badge-info">{{ $task->category?->name ?? 'General' }}</span>
            <span class="text-xs text-slate-400">{{ $task->booked }}/{{ $task->amount }} booked</span>
        </div>
        <h3 class="font-semibold text-slate-800 mb-1 line-clamp-2">{{ $task->title }}</h3>
        <p class="text-sm text-slate-500 line-clamp-3 flex-1 mb-3">{{ $task->details }}</p>
        <div class="flex items-center justify-between pt-3 border-t border-slate-100">
            <span class="text-blue-600 font-bold text-lg">${{ number_format((float)$task->price,2) }}</span>
            <a href="{{ route('user.task',$task) }}" class="btn btn-primary text-xs">View & Book</a>
        </div>
    </div>
    @endforeach
</div>
<div class="mt-6">{{ $tasks->links() }}</div>
@else
<div class="card p-12 text-center text-slate-400"><p class="text-4xl mb-3">📭</p><p>No tasks available right now. Check back soon!</p></div>
@endif
@endsection
