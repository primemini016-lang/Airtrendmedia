@extends('layouts.admin')

@section('title', 'Complaints')
@section('heading', 'Complaints & Disputes')

@section('content')
<div class="flex flex-wrap gap-2 mb-5">
    <a href="{{ route('admin.complaints') }}" class="px-4 py-2 rounded-lg text-sm font-semibold {{ !request('status') ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">All</a>
    @php $tabs = ['open' => 'Open', 'approved' => 'Worker Won', 'rejected' => 'Employer Won']; @endphp
    @foreach($tabs as $key => $label)
        <a href="{{ route('admin.complaints', ['status' => $key]) }}" class="px-4 py-2 rounded-lg text-sm font-semibold {{ request('status') === $key ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">{{ $label }}</a>
    @endforeach
</div>

@if($complaints->isEmpty())
    <div class="card text-center py-16"><div class="text-5xl mb-3"><x-icon name="complaints" class="w-4 h-4 inline" /></div><h3 class="font-bold text-slate-800">No complaints</h3><p class="text-slate-500 mt-1">All clear — no disputes to resolve.</p></div>
@else
    <div class="grid md:grid-cols-2 gap-4">
        @foreach($complaints as $c)
            @php $st = [1=>['Open','warning'],2=>['Worker Won','success'],3=>['Employer Won','danger']][$c->status] ?? ['Unknown','muted']; @endphp
            <div class="card">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-3">
                        <div class="text-sm">
                            <p class="text-slate-400">Task</p>
                            <p class="font-semibold text-slate-800">{{ $c->proof->task->title ?? '—' }}</p>
                        </div>
                        <span class="badge badge-{{ $st[1] }}">{{ $st[0] }}</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-sm mb-3">
                        <div><p class="text-slate-400 text-xs">Worker</p><p class="text-slate-700">{{ $c->user->name }}</p></div>
                        <div><p class="text-slate-400 text-xs">Employer</p><p class="text-slate-700">{{ $c->user2->name }}</p></div>
                    </div>
                    <div class="bg-slate-50 rounded-lg p-3 text-sm text-slate-600 mb-3">
                        <p class="text-xs text-slate-400 mb-1">Complaint details:</p>
                        {{ $c->details }}
                    </div>
                    @if($c->reply)
                        <div class="bg-blue-50 rounded-lg p-3 text-sm text-slate-600 mb-3"><p class="text-xs text-slate-400 mb-1">Admin reply:</p>{{ $c->reply }}</div>
                    @endif
                    @if($c->status === 1)
                        <form action="{{ route('admin.complaints.resolve', $c) }}" method="POST">
                            @csrf
                            <div class="mb-2"><textarea name="reply" class="input text-sm" rows="2" placeholder="Optional reply/explanation"></textarea></div>
                            <input type="hidden" name="status" value="2">
                            <button class="btn btn-success text-sm w-full mb-2" onclick="return confirm('Resolve in favour of the WORKER? They will be paid.')">Worker Wins (Pay Worker)</button>
                        </form>
                        <form action="{{ route('admin.complaints.resolve', $c) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="3">
                            <button class="btn btn-danger text-sm w-full" onclick="return confirm('Resolve in favour of the EMPLOYER? They will be refunded.')">Employer Wins (Refund Employer)</button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
    <div class="mt-6">{{ $complaints->links() }}</div>
@endif
@endsection
