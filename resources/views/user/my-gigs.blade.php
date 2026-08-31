@extends('layouts.user')

@section('title', 'My Gigs')
@section('heading', 'My Gigs')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100">My Gigs</h2>
            <p class="text-slate-500 text-sm">Manage the freelance services you offer.</p>
        </div>
        <a href="{{ route('user.gigs.create') }}" class="btn btn-primary"><x-icon name="plus" class="w-4 h-4" /> Create New Gig</a>
    </div>

    @include('partials.alerts')

    @if($gigs->isNotEmpty())
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($gigs as $gig)
        <div class="card overflow-hidden flex flex-col">
            @if($gig->image)
                <img src="{{ storage_asset($gig->image) }}" class="w-full h-36 object-cover" alt="{{ $gig->title }}">
            @else
                <div class="w-full h-36 bg-gradient-to-br from-blue-100 to-indigo-200 flex items-center justify-center">
                    <x-icon name="gigs" class="w-10 h-10 text-blue-400" />
                </div>
            @endif
            <div class="p-4 flex flex-col flex-1">
                <div class="flex items-center justify-between mb-2">
                    <span class="badge badge-info">{{ $gig->category?->name ?? 'General' }}</span>
                    @if($gig->status === 'active')<span class="badge badge-success">Active</span>
                    @elseif($gig->status === 'pending')<span class="badge badge-warning">Pending</span>
                    @elseif($gig->status === 'paused')<span class="badge badge-muted">Paused</span>
                    @elseif($gig->status === 'rejected')<span class="badge badge-danger">Rejected</span>
                    @endif
                </div>
                <h3 class="font-semibold text-slate-800 dark:text-slate-100 line-clamp-2 mb-1">{{ $gig->title }}</h3>
                <p class="text-xs text-slate-400 line-clamp-2 mb-3">{{ $gig->description }}</p>
                <div class="flex items-center justify-between mt-auto pt-3 border-t border-slate-100 dark:border-slate-700">
                    <span class="text-blue-600 font-bold">{{ money((float)$gig->price) }}</span>
                    <div class="flex gap-2">
                        <a href="{{ route('user.gigs.edit', $gig) }}" class="btn btn-outline text-xs px-3 py-1.5"><x-icon name="edit" class="w-3.5 h-3.5" /> Edit</a>
                        @if($gig->status === 'active')
                            <a href="{{ route('gigs.show', $gig) }}" target="_blank" class="btn btn-outline text-xs px-3 py-1.5"><x-icon name="eye" class="w-3.5 h-3.5" /> View</a>
                        @endif
                    </div>
                </div>
                <form action="{{ route('user.gigs.destroy', $gig) }}" method="POST" class="mt-2" onsubmit="return confirm('Delete this gig permanently?')">
                    @csrf @method('DELETE')
                    <button class="text-xs text-red-500 hover:text-red-700 flex items-center gap-1"><x-icon name="trash" class="w-3.5 h-3.5" /> Delete</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    <div class="mt-4">{{ $gigs->links() }}</div>
    @else
    <div class="card">
        <div class="card-body text-center py-12">
            <x-icon name="gigs" class="w-12 h-12 mx-auto text-slate-300 mb-3" />
            <p class="text-slate-500 mb-4">You haven't created any gigs yet.</p>
            <a href="{{ route('user.gigs.create') }}" class="btn btn-primary"><x-icon name="plus" class="w-4 h-4" /> Create Your First Gig</a>
        </div>
    </div>
    @endif
</div>
@endsection
