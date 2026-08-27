@extends('layouts.admin')

@section('title', 'Users')
@section('heading', 'Users')

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="label">Search</label>
                <input type="text" name="q" class="input" value="{{ request('q') }}" placeholder="Name, username, or email…">
            </div>
            <div>
                <label class="label">Status</label>
                <select name="active" class="input">
                    <option value="">All</option>
                    <option value="1" {{ request('active') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div>
                <label class="label">Banned</label>
                <select name="banned" class="input">
                    <option value="">All</option>
                    <option value="1" {{ request('banned') === '1' ? 'selected' : '' }}>Banned</option>
                </select>
            </div>
            <button class="btn btn-primary">Filter</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="overflow-x-auto">
        <table class="tbl">
            <thead>
                <tr><th>User</th><th>Email</th><th>Balance</th><th>Status</th><th>Joined</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                    <tr>
                        <td>
                            <div class="flex items-center gap-2">
                                @if($u->image)<img src="{{ asset('storage/'.$u->image) }}" class="w-8 h-8 rounded-full object-cover">@else<div class="w-8 h-8 rounded-full auth-gradient flex items-center justify-center text-white text-xs font-bold">{{ strtoupper(substr($u->name,0,1)) }}</div>@endif
                                <div><p class="font-semibold text-slate-800">{{ $u->name }}</p><p class="text-xs text-slate-400">{{ $u->username }}</p></div>
                            </div>
                        </td>
                        <td class="text-slate-600 text-sm">{{ $u->email }}</td>
                        <td class="font-semibold">{{ number_format((float)$u->balance,2) }} USD</td>
                        <td>
                            @if($u->banned)<span class="badge badge-danger">Banned</span>@elseif($u->is_active)<span class="badge badge-success">Active</span>@else<span class="badge badge-warning">Inactive</span>@endif
                        </td>
                        <td class="text-slate-500 text-sm">{{ $u->created_at->format('M d, Y') }}</td>
                        <td><a href="{{ route('admin.users.show', $u) }}" class="text-blue-600 hover:underline text-sm font-semibold">Manage <x-icon name="arrow-right" class="w-4 h-4 inline" /></a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-slate-400 py-8">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $users->links() }}</div>
</div>
@endsection
