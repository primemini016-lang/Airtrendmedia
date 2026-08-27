@extends('layouts.admin')

@section('title', 'Withdrawals')
@section('heading', 'Withdrawals')

@section('content')
<div class="flex flex-wrap gap-2 mb-5">
    <a href="{{ route('admin.withdrawals') }}" class="px-4 py-2 rounded-lg text-sm font-semibold {{ !request('status') ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">All</a>
    @php $tabs = ['pending' => 'Pending', 'paid' => 'Paid', 'rejected' => 'Rejected']; @endphp
    @foreach($tabs as $key => $label)
        <a href="{{ route('admin.withdrawals', ['status' => $key]) }}" class="px-4 py-2 rounded-lg text-sm font-semibold {{ request('status') === $key ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">{{ $label }}</a>
    @endforeach
</div>

<div class="card">
    <div class="overflow-x-auto">
        <table class="tbl">
            <thead>
                <tr><th>User</th><th>Method</th><th>Amount</th><th>Fee</th><th>Paid</th><th>Details</th><th>Date</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @forelse($withdrawals as $w)
                    @php $st = [0=>['Pending','warning'],1=>['Paid','success'],2=>['Rejected','danger']][$w->status] ?? ['Unknown','muted']; @endphp
                    <tr>
                        <td><a href="{{ route('admin.users.show', $w->user) }}" class="font-semibold text-slate-800 hover:text-blue-600">{{ $w->user->name }}</a><div class="text-xs text-slate-400">{{ $w->user->email }}</div></td>
                        <td class="text-sm text-slate-600">{{ $w->method->name ?? '—' }}</td>
                        <td class="font-semibold">{{ number_format($w->amount,2) }}</td>
                        <td class="text-slate-500">{{ number_format($w->fee,2) }}</td>
                        <td class="font-semibold text-blue-600">{{ number_format($w->paid,2) }}</td>
                        <td class="text-xs text-slate-500 max-w-[200px] truncate" title="{{ $w->details }}">{{ $w->details }}</td>
                        <td class="text-sm text-slate-500">{{ $w->date }}</td>
                        <td><span class="badge badge-{{ $st[1] }}">{{ $st[0] }}</span></td>
                        <td>
                            @if($w->status === 0)
                                <form action="{{ route('admin.withdrawals.paid', $w) }}" method="POST" class="inline">@csrf<button class="btn btn-success text-xs" onclick="return confirm('Mark this withdrawal as paid?')">Mark Paid</button></form>
                                <form action="{{ route('admin.withdrawals.reject', $w) }}" method="POST" class="inline mt-1">@csrf<input type="text" name="reject_note" placeholder="Reason" class="input text-xs w-24 inline-block" required><button class="btn btn-danger text-xs">Reject</button></form>
                            @else
                                <span class="text-slate-300 text-xs">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-slate-400 py-8">No withdrawals found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $withdrawals->links() }}</div>
</div>
@endsection
