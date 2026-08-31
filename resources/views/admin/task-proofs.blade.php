@extends('layouts.admin')

@section('title', 'Task Proofs')
@section('heading', 'Proofs — ' . $task->title)

@section('content')
<div class="mb-5">
    <a href="{{ route('admin.tasks') }}" class="text-blue-600 hover:underline text-sm"><x-icon name="arrow-left" class="w-4 h-4 inline" /> Back to Tasks</a>
</div>

<div class="card mb-6">
    <div class="card-body grid sm:grid-cols-4 gap-4 text-sm">
        <div>
            <p class="text-slate-400 text-xs uppercase">Unit Price</p>
            <p class="font-bold text-blue-600">{{ money((float)$task->price) }}</p>
        </div>
        <div>
            <p class="text-slate-400 text-xs uppercase">Slots</p>
            <p class="font-bold text-slate-800">{{ $task->booked }}/{{ $task->amount }} booked</p>
        </div>
        <div>
            <p class="text-slate-400 text-xs uppercase">Completed</p>
            <p class="font-bold text-slate-800">{{ $task->completed }}/{{ $task->amount }}</p>
        </div>
        <div>
            <p class="text-slate-400 text-xs uppercase">Action URL</p>
            @if($task->action_url)
                <a href="{{ $task->action_url }}" target="_blank" rel="noopener" class="text-blue-600 hover:underline break-all">{{ $task->action_url }}</a>
            @else
                <p class="text-slate-400">—</p>
            @endif
        </div>
    </div>
</div>

<div class="flex flex-wrap gap-2 mb-5">
    @php $tabs = ['' => 'All', '0' => 'Pending', '1' => 'Approved', '2' => 'Rejected']; @endphp
    @foreach($tabs as $key => $label)
        <a href="{{ route('admin.tasks.proofs', [$task, 'status' => $key]) }}" class="px-4 py-2 rounded-lg text-sm font-semibold {{ (string)request('status', '') === (string)$key ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">{{ $label }}</a>
    @endforeach
</div>

@if($proofs->isEmpty())
    <div class="card text-center py-16">
        <div class="text-5xl mb-3"><x-icon name="image" class="w-4 h-4 inline" /></div>
        <h3 class="text-lg font-bold text-slate-800">No proofs submitted yet</h3>
        <p class="text-slate-500 mt-1">When workers submit proofs of completion, they will appear here for your review.</p>
    </div>
@else
    <div class="grid gap-4 md:grid-cols-2">
        @foreach($proofs as $proof)
            @php
                $statusMap = [0 => ['Pending Review','warning'], 1 => ['Approved','success'], 2 => ['Rejected','danger'], 3 => ['Disputed','danger']];
                $st = $statusMap[$proof->status] ?? ['Unknown','muted'];
                $images = is_string($proof->images) ? json_decode($proof->images, true) : $proof->images;
                $images = $images ?? [];
            @endphp
            <div class="card">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            @if($proof->user->image)<img src="{{ storage_asset($proof->user->image) }}" class="w-8 h-8 rounded-full object-cover">@else<div class="w-8 h-8 rounded-full auth-gradient flex items-center justify-center text-white text-xs font-bold">{{ strtoupper(substr($proof->user->username,0,1)) }}</div>@endif
                            <div>
                                <p class="font-semibold text-slate-800 text-sm">
                                    <a href="{{ route('user.public-profile', $proof->user) }}" class="hover:underline">{{ $proof->user->name }}</a>
                                    @if($proof->user->isBlueVerified())<x-verified-badge size="w-4 h-4 inline" />@endif
                                </p>
                                <p class="text-xs text-slate-400">{{ '@' . $proof->user->username }}</p>
                            </div>
                        </div>
                        <span class="badge badge-{{ $st[1] }}">{{ $st[0] }}</span>
                    </div>

                    @if($proof->comment)
                        <p class="text-sm text-slate-600 bg-slate-50 rounded-lg p-3 mb-3">{{ $proof->comment }}</p>
                    @endif

                    @if(!empty($images))
                        <div class="flex flex-wrap gap-2 mb-3">
                            @foreach($images as $img)
                                <a href="{{ storage_asset($img) }}" target="_blank">
                                    <img src="{{ storage_asset($img) }}" class="w-20 h-20 object-cover rounded-lg border border-slate-200">
                                </a>
                            @endforeach
                        </div>
                    @endif

                    @if($proof->status === 2 && $proof->reject_note)
                        <p class="text-xs text-red-600 bg-red-50 rounded-lg p-2 mb-3">Rejection reason: {{ $proof->reject_note }}</p>
                    @endif

                    @if($proof->status === 0)
                        <form action="{{ route('admin.proofs.approve', $proof) }}" method="POST" class="inline">
                            @csrf
                            <button class="btn btn-success text-sm" onclick="return confirm('Approve this proof and credit the worker {{ money((float)$task->price) }}?')">Approve & Pay</button>
                        </form>
                        <form action="{{ route('admin.proofs.reject', $proof) }}" method="POST" class="inline">
                            @csrf
                            <input type="text" name="reject_note" placeholder="Reason for rejection" class="input text-sm inline-block w-48" required>
                            <button class="btn btn-danger text-sm">Reject</button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-6">{{ $proofs->links() }}</div>
@endif
@endsection
