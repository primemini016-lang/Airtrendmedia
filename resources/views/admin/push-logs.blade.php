@extends('layouts.admin')

@section('title', 'Push Logs')
@section('heading', 'Push Notification Logs')

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.push-settings') }}" class="btn btn-ghost text-sm">← Back to Settings</a>
    </div>

    <div class="card">
        <div class="card-body">
            @if($logs->isEmpty())
                <p class="text-center text-slate-400 py-8">No push notification logs.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-slate-400 border-b border-slate-200 dark:border-slate-700">
                                <th class="pb-2 pr-4">Title</th>
                                <th class="pb-2 pr-4">Recipient</th>
                                <th class="pb-2 pr-4">Status</th>
                                <th class="pb-2 pr-4">Sent</th>
                                <th class="pb-2">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr class="border-b border-slate-100 dark:border-slate-800">
                                    <td class="py-3 pr-4 text-slate-700 dark:text-slate-200">{{ $log->title ?? $log->message ?? '—' }}</td>
                                    <td class="py-3 pr-4 text-slate-500">{{ $log->user_id ? 'User #'.$log->user_id : 'Broadcast' }}</td>
                                    <td class="py-3 pr-4">
                                        <span class="badge @if(($log->status ?? '') === 'sent') badge-success @elseif(($log->status ?? '') === 'failed') badge-danger @else badge-warning @endif">{{ $log->status ?? '—' }}</span>
                                    </td>
                                    <td class="py-3 pr-4 text-xs text-slate-400">{{ $log->created_at->format('M d, Y H:i') }}</td>
                                    <td class="py-3 text-xs text-slate-400 max-w-xs truncate">{{ $log->error ?? $log->provider_response ?? '' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $logs->links() }}
            @endif
        </div>
    </div>
</div>
@endsection
