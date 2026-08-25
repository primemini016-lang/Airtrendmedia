@extends('layouts.admin')

@section('title', 'Deposits')
@section('heading', 'Deposits')

@section('content')
<div class="flex flex-wrap gap-2 mb-5">
    <a href="{{ route('admin.deposits') }}" class="px-4 py-2 rounded-lg text-sm font-semibold {{ !request('status') ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">All</a>
    @php $tabs = ['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected']; @endphp
    @foreach($tabs as $key => $label)
        <a href="{{ route('admin.deposits', ['status' => $key]) }}" class="px-4 py-2 rounded-lg text-sm font-semibold {{ request('status') === $key ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">{{ $label }}</a>
    @endforeach
</div>

<div class="card">
    <div class="overflow-x-auto">
        <table class="tbl">
            <thead>
                <tr><th>User</th><th>Type</th><th>Amount (USD)</th><th>Paid</th><th>Reference</th><th>Date</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @forelse($deposits as $d)
                    @php $st = [0=>['Pending','warning'],1=>['Completed','success'],2=>['Rejected','danger']][$d->status] ?? ['Unknown','muted']; @endphp
                    <tr>
                        <td><a href="{{ route('admin.users.show', $d->user) }}" class="font-semibold text-slate-800 hover:text-blue-600">{{ $d->user->name }}</a><div class="text-xs text-slate-400">{{ $d->user->email }}</div></td>
                        <td><span class="badge badge-muted">{{ ucfirst($d->type) }}</span></td>
                        <td class="font-semibold">{{ number_format($d->amount,2) }}</td>
                        <td class="text-slate-600">{{ number_format($d->amount_paid,2) }}</td>
                        <td class="text-xs text-slate-400">{{ $d->reference }}</td>
                        <td class="text-sm text-slate-500">{{ $d->date }}</td>
                        <td><span class="badge badge-{{ $st[1] }}">{{ $st[0] }}</span></td>
                        <td>
                            @if($d->status === 0)
                                <form action="{{ route('admin.deposits.approve', $d) }}" method="POST" class="inline">@csrf<button class="btn btn-success text-xs" onclick="return confirm('Approve this deposit?')">Approve</button></form>
                                <form action="{{ route('admin.deposits.reject', $d) }}" method="POST" class="inline mt-1">@csrf<input type="text" name="reject_note" placeholder="Reason" class="input text-xs w-24 inline-block" required><button class="btn btn-danger text-xs">Reject</button></form>
                            @else
                                <span class="text-slate-300 text-xs">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-slate-400 py-8">No deposits found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $deposits->links() }}</div>
</div>
@endsection
