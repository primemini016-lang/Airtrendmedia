@extends('layouts.admin')

@section('title', 'Tasks')
@section('heading', 'Tasks')

@section('content')
<div class="flex flex-wrap gap-2 mb-5">
    <a href="{{ route('admin.tasks') }}" class="px-4 py-2 rounded-lg text-sm font-semibold {{ !request('status') ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">All</a>
    @php $tabs = ['pending' => 'Pending', 'active' => 'Active', 'completed' => 'Completed', 'rejected' => 'Rejected', 'full' => 'Full']; @endphp
    @foreach($tabs as $key => $label)
        <a href="{{ route('admin.tasks', ['status' => $key]) }}" class="px-4 py-2 rounded-lg text-sm font-semibold {{ request('status') === $key ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">{{ $label }}</a>
    @endforeach
</div>

<div class="card">
    <div class="overflow-x-auto">
        <table class="tbl">
            <thead>
                <tr><th>Task</th><th>Employer</th><th>Category</th><th>Price</th><th>Slots</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @forelse($tasks as $t)
                    @php $st = [0=>['Pending','warning'],1=>['Active','success'],2=>['Completed','info'],3=>['Rejected','danger'],4=>['Full','muted']][$t->status] ?? ['Unknown','muted']; @endphp
                    <tr>
                        <td><div class="font-semibold text-slate-800">{{ $t->title }}</div><div class="text-xs text-slate-400">{{ $t->code }}</div></td>
                        <td class="text-sm text-slate-600">{{ $t->user->name }}</td>
                        <td class="text-sm text-slate-600">{{ $t->category->name ?? '—' }}</td>
                        <td class="font-semibold">{{ number_format($t->price,2) }}</td>
                        <td class="text-sm">{{ $t->booked }}/{{ $t->amount }}</td>
                        <td><span class="badge badge-{{ $st[1] }}">{{ $st[0] }}</span></td>
                        <td>
                            @if($t->status === 0)
                                <form action="{{ route('admin.tasks.approve', $t) }}" method="POST" class="inline">@csrf<button class="btn btn-success text-xs">Approve</button></form>
                                <form action="{{ route('admin.tasks.reject', $t) }}" method="POST" class="inline mt-1">@csrf<input type="text" name="reject_note" placeholder="Reason" class="input text-xs w-24 inline-block" required><button class="btn btn-danger text-xs">Reject</button></form>
                            @endif
                            <form action="{{ route('admin.tasks.delete', $t) }}" method="POST" class="inline mt-1">@csrf@method('DELETE')<button class="btn btn-danger text-xs" onclick="return confirm('Permanently delete this task? (Super admin only)')">Delete</button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-slate-400 py-8">No tasks found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $tasks->links() }}</div>
</div>
@endsection
