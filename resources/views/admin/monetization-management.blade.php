@extends('layouts.admin')

@section('title', 'Monetization Management')
@section('heading', 'Monetization Management (God-Mode)')

@section('content')
<div class="space-y-6">

    {{-- Filters --}}
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.monetization-management') }}" class="badge {{ !request('status') ? 'badge-success' : 'badge-default' }}">All</a>
        <a href="{{ route('admin.monetization-management', ['status'=>'enabled']) }}" class="badge {{ request('status')==='enabled' ? 'badge-success' : 'badge-default' }}">Enabled</a>
        <a href="{{ route('admin.monetization-management', ['status'=>'disabled']) }}" class="badge {{ request('status')==='disabled' ? 'badge-danger' : 'badge-default' }}">Disabled</a>
        <a href="{{ route('admin.monetization-management', ['status'=>'eligible']) }}" class="badge {{ request('status')==='eligible' ? 'badge-warning' : 'badge-default' }}">Eligible</a>
    </div>

    {{-- Monetized users --}}
    <div class="card">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 dark:text-slate-100 mb-4">User Monetization Status</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Requirements: 500 paid followers ($5 activation) + 1,000 eligible views + 1,000 real engagement → auto-enabled.</p>

            @if($eligibilities->isEmpty())
                <p class="text-center text-slate-400 py-8">No monetization records found.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-slate-400 border-b border-slate-200 dark:border-slate-700">
                                <th class="pb-2 pr-4">User</th>
                                <th class="pb-2 pr-4">Paid Followers</th>
                                <th class="pb-2 pr-4">Eligible Views</th>
                                <th class="pb-2 pr-4">Engagement</th>
                                <th class="pb-2 pr-4">Eligible</th>
                                <th class="pb-2 pr-4">Enabled</th>
                                <th class="pb-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($eligibilities as $elig)
                                @php $u = $elig->user; @endphp
                                <tr class="border-b border-slate-100 dark:border-slate-800">
                                    <td class="py-3 pr-4">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-full auth-gradient flex items-center justify-center text-white text-xs font-bold">{{ strtoupper(substr($u->name ?? 'U',0,1)) }}</div>
                                            <div>
                                                <p class="text-slate-700 dark:text-slate-200">{{ $u->name ?? '—' }}</p>
                                                <p class="text-xs text-slate-400">{{ $u->email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 pr-4 text-slate-600">{{ number_format($elig->paid_followers ?? 0) }} / 500</td>
                                    <td class="py-3 pr-4 text-slate-600">{{ number_format($elig->eligible_views ?? 0) }} / 1,000</td>
                                    <td class="py-3 pr-4 text-slate-600">{{ number_format($elig->real_engagement ?? 0) }} / 1,000</td>
                                    <td class="py-3 pr-4">
                                        @if($elig->is_eligible)<span class="badge badge-success">Yes</span>@else<span class="badge badge-default">No</span>@endif
                                    </td>
                                    <td class="py-3 pr-4">
                                        @if($u->monetization_enabled ?? false)
                                            <span class="badge badge-success">Active</span>
                                        @elseif($u->monetization_disabled_reason)
                                            <span class="badge badge-danger" title="{{ $u->monetization_disabled_reason }}">Disabled</span>
                                        @else
                                            <span class="badge badge-default">Off</span>
                                        @endif
                                    </td>
                                    <td class="py-3">
                                        @if($u->monetization_enabled ?? false)
                                            <button onclick="openDisable({{ $u->id }}, '{{ addslashes($u->name) }}')" class="text-red-600 text-xs hover:underline">Disable</button>
                                        @else
                                            <form action="{{ route('admin.monetization.disable', $u) }}" method="POST" style="display:none">@csrf</form>
                                            <form action="{{ route('admin.monetization.enable', $u) }}" method="POST">@csrf<button class="text-green-600 text-xs hover:underline" onclick="return confirm('Re-enable monetization for this user?')">Enable</button></form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $eligibilities->links() }}
            @endif
        </div>
    </div>

    {{-- Disable logs --}}
    <div class="card">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 dark:text-slate-100 mb-3">Disable / Enable Log</h3>
            @if($disableLogs->isEmpty())
                <p class="text-center text-slate-400 py-4">No monetization actions logged.</p>
            @else
                <div class="space-y-2">
                    @foreach($disableLogs as $log)
                        <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 dark:bg-slate-700/30 text-sm">
                            <div class="flex items-center gap-3">
                                <span class="badge @if(($log->action ?? '') === 'disabled') badge-danger @else badge-success @endif">{{ ucfirst($log->action ?? 'action') }}</span>
                                <span class="text-slate-700 dark:text-slate-200">{{ $log->user->name ?? '—' }}</span>
                                @if($log->reason)<span class="text-slate-500 text-xs">— {{ $log->reason }}</span>@endif
                            </div>
                            <span class="text-xs text-slate-400">{{ $log->created_at->diffForHumans() }} · by {{ $log->admin->name ?? 'System' }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Disable modal --}}
<div id="disable-modal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 rounded-xl max-w-md w-full p-6">
        <h3 class="font-bold text-slate-800 dark:text-slate-100 mb-2">Disable Monetization</h3>
        <p class="text-sm text-slate-500 mb-4">Disabling monetization for <span id="disable-user-name" class="font-semibold"></span></p>
        <form id="disable-form" action="" method="POST">
            @csrf
            <label class="form-label">Reason for disabling *</label>
            <textarea name="reason" required class="form-input" rows="3" placeholder="e.g. Violation of monetization policy, fake engagement detected..."></textarea>
            <div class="flex gap-2 mt-4">
                <button type="submit" class="btn btn-danger">Disable Monetization</button>
                <button type="button" onclick="closeDisable()" class="btn btn-ghost">Cancel</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openDisable(userId, userName) {
    document.getElementById('disable-user-name').textContent = userName;
    document.getElementById('disable-form').action = '{{ route("admin.monetization.disable", ":id") }}'.replace(':id', userId);
    const modal = document.getElementById('disable-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}
function closeDisable() {
    const modal = document.getElementById('disable-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>
@endpush
@endsection
