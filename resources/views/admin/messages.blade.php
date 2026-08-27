@extends('layouts.admin')

@section('title', 'Support Messages')
@section('heading', 'Support Messages')

@section('content')
@if($users->isEmpty())
    <div class="card text-center py-16"><div class="text-5xl mb-3"><x-icon name="messages" class="w-4 h-4 inline" /></div><h3 class="font-bold text-slate-800">No conversations</h3><p class="text-slate-500 mt-1">User support messages will appear here.</p></div>
@else
    <div class="card">
        <div class="overflow-x-auto">
            <table class="tbl">
                <thead><tr><th>User</th><th>Email</th><th>Unread</th><th>Last Message</th><th></th></tr></thead>
                <tbody>
                    @foreach($users as $u)
                        <tr>
                            <td><div class="flex items-center gap-2"><div class="w-8 h-8 rounded-full auth-gradient flex items-center justify-center text-white text-xs font-bold">{{ strtoupper(substr($u->name,0,1)) }}</div><span class="font-semibold text-slate-800">{{ $u->name }}</span></div></td>
                            <td class="text-sm text-slate-600">{{ $u->email }}</td>
                            <td>@if($u->unread_count > 0)<span class="badge badge-danger">{{ $u->unread_count }} new</span>@else<span class="text-slate-300 text-sm">—</span>@endif</td>
                            <td class="text-sm text-slate-500">{{ $u->updated_at->diffForHumans() }}</td>
                            <td><a href="{{ route('admin.messages.show', $u) }}" class="text-blue-600 hover:underline text-sm font-semibold">Open <x-icon name="arrow-right" class="w-4 h-4 inline" /></a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $users->links() }}</div>
    </div>
@endif
@endsection
