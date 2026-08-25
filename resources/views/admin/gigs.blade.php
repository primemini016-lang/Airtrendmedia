@extends('layouts.admin')

@section('title', 'Gigs Management')
@section('heading', 'Gigs Moderation')

@section('content')
<div class="mb-6 flex gap-2 flex-wrap">
    <a href="{{ route('admin.gigs') }}" class="btn btn-outline text-xs {{ !request('status') ? 'btn-primary' : '' }}">All</a>
    <a href="{{ route('admin.gigs', ['status' => 'pending']) }}" class="btn btn-outline text-xs {{ request('status') === 'pending' ? 'btn-primary' : '' }}">Pending</a>
    <a href="{{ route('admin.gigs', ['status' => 'active']) }}" class="btn btn-outline text-xs {{ request('status') === 'active' ? 'btn-primary' : '' }}">Active</a>
    <a href="{{ route('admin.gigs', ['status' => 'rejected']) }}" class="btn btn-outline text-xs {{ request('status') === 'rejected' ? 'btn-primary' : '' }}">Rejected</a>
    <a href="{{ route('admin.gigs', ['status' => 'paused']) }}" class="btn btn-outline text-xs {{ request('status') === 'paused' ? 'btn-primary' : '' }}">Paused</a>
</div>

<div class="card">
    <div class="card-body">
        @if($gigs->isEmpty())
            <p class="text-slate-400 text-sm py-8 text-center">No gigs found.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-left text-slate-500 text-xs uppercase">
                            <th class="py-3 px-2">Gig</th>
                            <th class="py-3 px-2">Seller</th>
                            <th class="py-3 px-2">Category</th>
                            <th class="py-3 px-2">Price</th>
                            <th class="py-3 px-2">Platform</th>
                            <th class="py-3 px-2">Status</th>
                            <th class="py-3 px-2">Stats</th>
                            <th class="py-3 px-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($gigs as $gig)
                        <tr class="border-b border-slate-100">
                            <td class="py-3 px-2">
                                <div class="flex items-center gap-2">
                                    @if($gig->image)<img src="{{ Storage::url($gig->image) }}" class="w-10 h-10 rounded-lg object-cover" alt="">@endif
                                    <div>
                                        <p class="font-medium text-slate-800">{{ Str::limit($gig->title, 40) }}</p>
                                        <p class="text-xs text-slate-400">{{ $gig->created_at->format('M j, Y') }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-2 text-slate-600">{{ $gig->user?->username ?? '—' }}</td>
                            <td class="py-3 px-2 text-slate-600">{{ $gig->category?->name ?? '—' }}</td>
                            <td class="py-3 px-2 font-semibold text-slate-800">${{ number_format($gig->price, 2) }}</td>
                            <td class="py-3 px-2"><span class="badge badge-muted capitalize">{{ $gig->social_platform ?? '—' }}</span></td>
                            <td class="py-3 px-2">
                                @if($gig->status === 'active')<span class="badge badge-success">Active</span>
                                @elseif($gig->status === 'pending')<span class="badge badge-warning">Pending</span>
                                @elseif($gig->status === 'rejected')<span class="badge badge-danger">Rejected</span>
                                @else<span class="badge badge-muted">{{ ucfirst($gig->status) }}</span>@endif
                            </td>
                            <td class="py-3 px-2 text-xs text-slate-500">{{ $gig->views }} views / {{ $gig->sales }} sales</td>
                            <td class="py-3 px-2">
                                <div class="flex gap-1">
                                    @if($gig->status !== 'active')
                                    <form method="POST" action="{{ route('admin.gigs.approve', $gig) }}">
                                        @csrf <button class="btn btn-success text-xs px-2 py-1">Approve</button>
                                    </form>
                                    @endif
                                    @if($gig->status !== 'rejected')
                                    <form method="POST" action="{{ route('admin.gigs.reject', $gig) }}" onsubmit="return confirm('Reject this gig?')">
                                        @csrf
                                        <input type="hidden" name="reject_note" value="Does not meet quality standards.">
                                        <button class="btn btn-outline text-xs px-2 py-1">Reject</button>
                                    </form>
                                    @endif
                                    <form method="POST" action="{{ route('admin.gigs.delete', $gig) }}" onsubmit="return confirm('Delete this gig permanently?')">
                                        @csrf @method('DELETE') <button class="btn btn-danger text-xs px-2 py-1">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $gigs->links() }}</div>
        @endif
    </div>
</div>
@endsection
