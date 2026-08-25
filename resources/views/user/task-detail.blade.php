@extends('layouts.user')
@section('title', $task->title)
@section('heading', 'Task Details')

@section('content')
<a href="{{ route('user.tasks') }}" class="text-blue-600 text-sm hover:underline mb-4 inline-block">← Back to browse</a>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="card"><div class="card-body">
            <div class="flex items-center justify-between mb-3">
                <span class="badge badge-info">{{ $task->category?->name ?? 'General' }}</span>
                <span class="badge badge-success">{{ $task->booked }}/{{ $task->amount }} booked</span>
            </div>
            <h2 class="text-2xl font-bold text-slate-800 mb-2">{{ $task->title }}</h2>
            <p class="text-slate-600 leading-relaxed whitespace-pre-wrap">{{ $task->details }}</p>
            @if($task->action_url)
            <div class="mt-4 px-4 py-3 rounded-lg bg-slate-50 border border-slate-100">
                <p class="text-xs text-slate-400 mb-1">Action URL</p>
                <a href="{{ $task->action_url }}" target="_blank" class="text-blue-600 hover:underline break-all text-sm">{{ $task->action_url }} ↗</a>
            </div>
            @endif
        </div></div>

        @if($myProof)
        <div class="card"><div class="card-body">
            <h3 class="font-bold text-slate-800 mb-3">Your Submitted Proof</h3>
            @php $statusLabels = [0=>['Pending Review','badge-warning'],1=>['Approved & Paid','badge-success'],2=>['Rejected','badge-danger'],3=>['Disputed','badge-warning']]; $sl = $statusLabels[(int)$myProof->status] ?? ['Unknown','badge-muted']; @endphp
            <p><span class="badge {{ $sl[1] }}">{{ $sl[0] }}</span></p>
            <p class="text-slate-600 text-sm mt-3 whitespace-pre-wrap">{{ $myProof->comment }}</p>
            @if($myProof->reject_note)<p class="text-red-600 text-sm mt-2"><strong>Rejection reason:</strong> {{ $myProof->reject_note }}</p>@endif
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 mt-3">
                @foreach($myProof->images ?? [] as $img)<img src="{{ asset('storage/'.$img) }}" class="rounded-lg w-full h-28 object-cover">@endforeach
            </div>
        </div></div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <div class="card"><div class="card-body">
            <p class="text-slate-400 text-sm">Reward per task</p>
            <p class="text-3xl font-bold text-blue-600 mb-4">${{ number_format((float)$task->price,2) }}</p>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-slate-500">Slots</span><span class="font-semibold">{{ $task->booked }}/{{ $task->amount }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Posted by</span><span class="font-semibold">{{ $task->user?->username }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Posted on</span><span class="font-semibold">{{ $task->date->format('M d, Y') }}</span></div>
                @if($task->time)<div class="flex justify-between"><span class="text-slate-500">Est. time</span><span class="font-semibold">{{ $task->time }} min</span></div>@endif
            </div>

            @if($myBooking && !$myProof)
            <form action="{{ route('user.task', $task) }}/proof" method="POST" enctype="multipart/form-data" class="mt-5 space-y-3">
                @csrf
                <div>
                    <label class="label">Proof Comment</label>
                    <textarea name="comment" rows="3" class="input" required placeholder="Describe what you did..."></textarea>
                </div>
                <div>
                    <label class="label">Proof Images (1–5)</label>
                    <input type="file" name="images[]" multiple accept="image/*" class="input" required>
                </div>
                <button class="btn btn-primary w-full">Submit Proof</button>
            </form>
            @elseif(!$myBooking && $task->booked < $task->amount && $task->isActive())
            <form action="{{ route('user.task', $task) }}/book" method="POST" class="mt-5">
                @csrf
                <button class="btn btn-primary w-full">Book This Task</button>
            </form>
            @elseif($myBooking && $myProof)
            <p class="mt-5 text-sm text-slate-400 text-center">Proof already submitted.</p>
            @elseif(!$task->isActive())
            <p class="mt-5 text-sm text-slate-400 text-center">This task is no longer active.</p>
            @else
            <p class="mt-5 text-sm text-slate-400 text-center">All slots filled.</p>
            @endif
        </div></div>
    </div>
</div>
@endsection
