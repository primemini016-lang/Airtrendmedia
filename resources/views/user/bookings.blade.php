@extends('layouts.user')

@section('title', 'My Bookings')
@section('heading', 'My Bookings')

@section('content')
<div class="mb-5">
    <p class="text-slate-500 text-sm">Track all the tasks you have booked as a worker. Submit your proof of completion before each booking expires.</p>
</div>

<div class="flex flex-wrap gap-2 mb-5">
    @php
        $tabs = ['all' => 'All', 'pending' => 'Pending', 'submitted' => 'Submitted', 'approved' => 'Approved', 'rejected' => 'Rejected'];
    @endphp
    @foreach($tabs as $key => $label)
        <a href="{{ route('user.bookings', ['status' => $key]) }}"
           class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $status === $key ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:border-blue-300' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

@if($bookings->isEmpty())
    <div class="card text-center py-16">
        <div class="text-5xl mb-3"><x-icon name="send" class="w-4 h-4 inline" /></div>
        <h3 class="text-lg font-bold text-slate-800">No bookings found</h3>
        <p class="text-slate-500 mt-1 mb-4">You haven't booked any tasks in this category yet.</p>
        <a href="{{ route('user.tasks') }}" class="btn btn-primary">Browse Tasks</a>
    </div>
@else
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($bookings as $booking)
            @php
                $task = $booking->task;
                $proof = $task->proofs->where('user_id', auth()->id())->first();
                $proofStatus = $proof->status ?? null;
                $badge = match($proofStatus) { 0 => ['Submitted','warning'], 1 => ['Approved','success'], 2 => ['Rejected','danger'], default => ['Pending','muted'] };
            @endphp
            <div class="card flex flex-col">
                <div class="card-body flex-1">
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <a href="{{ route('user.task', $task) }}" class="font-bold text-slate-800 hover:text-blue-600 line-clamp-2">{{ $task->title }}</a>
                        <span class="badge badge-{{ $badge[1] }}">{{ $badge[0] }}</span>
                    </div>
                    <p class="text-xs text-slate-500 mb-3">By {{ $task->user->username }} · {{ $task->category->name }}</p>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-600">Reward</span>
                        <span class="font-bold text-blue-600">{{ number_format($task->price,2) }} USD</span>
                    </div>
                    <div class="flex items-center justify-between text-sm mt-1">
                        <span class="text-slate-600">Progress</span>
                        <span class="text-slate-700">{{ $task->booked }}/{{ $task->amount }}</span>
                    </div>
                </div>
                <div class="px-5 pb-5">
                    @if($proofStatus === null)
                        <a href="{{ route('user.task', $task) }}" class="btn btn-primary w-full text-sm">Submit Proof</a>
                    @elseif($proofStatus === 2)
                        <a href="{{ route('user.task', $task) }}" class="btn btn-danger w-full text-sm">Resubmit Proof</a>
                    @else
                        <a href="{{ route('user.task', $task) }}" class="btn btn-outline w-full text-sm">View Details</a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-6">{{ $bookings->links() }}</div>
@endif
@endsection
