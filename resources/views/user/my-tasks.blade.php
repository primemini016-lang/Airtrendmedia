@extends('layouts.user')

@section('title', 'My Offers')
@section('heading', 'My Offers')

@section('content')
<div class="flex items-center justify-between mb-5 flex-wrap gap-3">
    <p class="text-slate-500 text-sm">Tasks you created as an employer. Review worker submissions and approve or reject proofs.</p>
    <a href="{{ route('user.task.create') }}" class="btn btn-primary text-sm">+ Create New Task</a>
</div>

<div class="flex flex-wrap gap-2 mb-5">
    @php
        $tabs = ['all' => 'All', 'pending' => 'Pending Review', 'active' => 'Active', 'completed' => 'Completed', 'rejected' => 'Rejected', 'full' => 'Full'];
    @endphp
    @foreach($tabs as $key => $label)
        <a href="{{ route('user.offers', ['status' => $key]) }}"
           class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $status === $key ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:border-blue-300' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

@if($tasks->isEmpty())
    <div class="card text-center py-16">
        <div class="text-5xl mb-3"><x-icon name="briefcase" class="w-4 h-4 inline" /></div>
        <h3 class="text-lg font-bold text-slate-800">No offers yet</h3>
        <p class="text-slate-500 mt-1 mb-4">Create your first task to start hiring workers.</p>
        <a href="{{ route('user.task.create') }}" class="btn btn-primary">Create a Task</a>
    </div>
@else
    <div class="overflow-x-auto card">
        <table class="tbl">
            <thead>
                <tr>
                    <th>Task</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Slots</th>
                    <th>Status</th>
                    <th>Proofs</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($tasks as $task)
                    @php
                        $statusMap = [0 => ['Pending','warning'], 1 => ['Active','success'], 2 => ['Completed','info'], 3 => ['Rejected','danger'], 4 => ['Full','muted']];
                        $st = $statusMap[$task->status];
                        $pendingProofs = $task->proofs->where('status', 0)->count();
                    @endphp
                    <tr>
                        <td>
                            <div class="font-semibold text-slate-800">{{ $task->title }}</div>
                            <div class="text-xs text-slate-400">{{ $task->code }}</div>
                        </td>
                        <td class="text-slate-600">{{ $task->category->name }}</td>
                        <td class="font-semibold text-blue-600">{{ number_format($task->price,2) }} USD</td>
                        <td>{{ $task->booked }}/{{ $task->amount }}</td>
                        <td><span class="badge badge-{{ $st[1] }}">{{ $st[0] }}</span></td>
                        <td>
                            @if($pendingProofs > 0)
                                <span class="badge badge-warning">{{ $pendingProofs }} to review</span>
                            @else
                                <span class="text-slate-400 text-sm">—</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('user.task.proofs', $task) }}" class="text-blue-600 hover:underline text-sm font-semibold">Manage</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $tasks->links() }}</div>
@endif
@endsection
