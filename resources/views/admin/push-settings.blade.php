@extends('layouts.admin')

@section('title', 'Push Notifications')
@section('heading', 'Push Notification Settings')

@section('content')
<div class="space-y-6">

    {{-- Status cards --}}
    <div class="grid sm:grid-cols-3 gap-4">
        <div class="card">
            <div class="card-body">
                <p class="stat-label">Active Device Tokens</p>
                <p class="stat-value text-blue-600">{{ number_format($totalTokens) }}</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <p class="stat-label">Provider</p>
                <p class="stat-value text-slate-700 dark:text-slate-200 capitalize">{{ $s->push_provider ?? 'none' }}</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <p class="stat-label">Push Enabled</p>
                <p class="stat-value @if($s->push_enabled) text-green-600 @else text-slate-500 @endif">{{ $s->push_enabled ? 'Yes' : 'No' }}</p>
            </div>
        </div>
    </div>

    {{-- Main settings form --}}
    <form action="{{ route('admin.push-settings.save') }}" method="POST">
        @csrf
        <div class="card">
            <div class="card-body">
                <h3 class="font-bold text-slate-800 dark:text-slate-100 mb-4">Provider Configuration</h3>

                <div class="grid sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="form-label">Push Provider</label>
                        <select name="push_provider" class="form-input">
                            <option value="none" @selected(($s->push_provider ?? 'none') === 'none')>None (Disabled)</option>
                            <option value="firebase" @selected(($s->push_provider ?? '') === 'firebase')>Firebase (FCM)</option>
                            <option value="onesignal" @selected(($s->push_provider ?? '') === 'onesignal')>OneSignal</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Enable Push</label>
                        <label class="flex items-center gap-3 mt-2">
                            <input type="checkbox" name="push_enabled" value="1" class="w-5 h-5 rounded" @if($s->push_enabled) checked @endif>
                            <span class="text-sm text-slate-600 dark:text-slate-300">Send push notifications to users</span>
                        </label>
                    </div>
                </div>

                {{-- Firebase section --}}
                <div class="border-t border-slate-200 dark:border-slate-700 pt-4 mt-4">
                    <h4 class="font-semibold text-blue-600 mb-3 flex items-center gap-2">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M3.89 15.673L6.59.341A.5.5 0 0 1 7.64.045l3.05 2.39a1 1 0 0 0 1.2 0L14.74.234a.5.5 0 0 1 .79.297l2.6 15.142a.5.5 0 0 1-.49.587H4.38a.5.5 0 0 1-.49-.587z" opacity=".3"/><path d="M3.89 15.673L6.59.341A.5.5 0 0 1 7.64.045l3.05 2.39a1 1 0 0 0 1.2 0L14.74.234a.5.5 0 0 1 .79.297l2.6 15.142a.5.5 0 0 1-.49.587H4.38a.5.5 0 0 1-.49-.587z"/></svg>
                        Firebase Cloud Messaging (FCM)
                    </h4>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="form-label">Server Key (Legacy HTTP API)</label>
                            <input type="password" name="firebase_server_key" class="form-input font-mono text-xs" value="{{ $s->firebase_server_key ?? '' }}" placeholder="AAAA:your-legacy-server-key">
                        </div>
                        <div><label class="form-label">Project ID</label><input type="text" name="firebase_project_id" class="form-input" value="{{ $s->firebase_project_id ?? '' }}" placeholder="airtrendmedia-prod"></div>
                        <div><label class="form-label">API Key</label><input type="text" name="firebase_api_key" class="form-input" value="{{ $s->firebase_api_key ?? '' }}" placeholder="AIza..."></div>
                        <div><label class="form-label">Auth Domain</label><input type="text" name="firebase_auth_domain" class="form-input" value="{{ $s->firebase_auth_domain ?? '' }}" placeholder="project.firebaseapp.com"></div>
                        <div><label class="form-label">Messaging Sender ID</label><input type="text" name="firebase_messaging_sender_id" class="form-input" value="{{ $s->firebase_messaging_sender_id ?? '' }}" placeholder="123456789"></div>
                        <div><label class="form-label">App ID</label><input type="text" name="firebase_app_id" class="form-input" value="{{ $s->firebase_app_id ?? '' }}" placeholder="1:123:web:abc"></div>
                        <div><label class="form-label">Measurement ID</label><input type="text" name="firebase_measurement_id" class="form-input" value="{{ $s->firebase_measurement_id ?? '' }}" placeholder="G-XXXXXXX"></div>
                        <div><label class="form-label">Storage Bucket</label><input type="text" name="firebase_storage_bucket" class="form-input" value="{{ $s->firebase_storage_bucket ?? '' }}" placeholder="project.appspot.com"></div>
                        <div class="sm:col-span-2">
                            <label class="form-label">Service Account JSON (optional, for Admin SDK)</label>
                            <textarea name="firebase_service_account_json" class="form-input font-mono text-xs" rows="3" placeholder='{"type":"service_account",...}'>{{ $s->firebase_service_account_json ?? '' }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- OneSignal section --}}
                <div class="border-t border-slate-200 dark:border-slate-700 pt-4 mt-4">
                    <h4 class="font-semibold text-red-600 mb-3">OneSignal</h4>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div><label class="form-label">App ID</label><input type="text" name="onesignal_app_id" class="form-input" value="{{ $s->onesignal_app_id ?? '' }}" placeholder="xxxxxxxx-xxxx-xxxx"></div>
                        <div><label class="form-label">REST API Key</label><input type="password" name="onesignal_rest_api_key" class="form-input" value="{{ $s->onesignal_rest_api_key ?? '' }}" placeholder="os_v2_app_..."></div>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                </div>
            </div>
        </div>
    </form>

    {{-- Test push --}}
    <div class="card">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 dark:text-slate-100 mb-3">Test Push Notification</h3>
            <form action="{{ route('admin.push-settings.test') }}" method="POST" class="flex flex-wrap items-end gap-3">
                @csrf
                <div class="flex-1 min-w-[200px]">
                    <label class="form-label">Target User (leave blank for broadcast)</label>
                    <input type="number" name="target_user_id" class="form-input" placeholder="User ID">
                </div>
                <button type="submit" class="btn btn-primary">Send Test</button>
            </form>
        </div>
    </div>

    {{-- Broadcast (Phoenix-style) --}}
    <div class="card">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 dark:text-slate-100 mb-1">Broadcast to All Users</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Send a Phoenix-style notification with optional images and slides to all registered devices.</p>
            <form action="{{ route('admin.push-settings.broadcast') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid sm:grid-cols-2 gap-4">
                    <div><label class="form-label">Title *</label><input type="text" name="title" required class="form-input" placeholder="Notification title"></div>
                    <div><label class="form-label">Icon URL</label><input type="text" name="icon" class="form-input" placeholder="/icons/icon-192.png"></div>
                </div>
                <div><label class="form-label">Body *</label><textarea name="body" required class="form-input" rows="2" placeholder="Notification message..."></textarea></div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div><label class="form-label">Image URL</label><input type="url" name="image_url" class="form-input" placeholder="https://example.com/image.jpg"></div>
                    <div><label class="form-label">Link</label><input type="url" name="link" class="form-input" placeholder="https://airtrendmedia.com"></div>
                </div>
                <div>
                    <label class="form-label">Slides (Phoenix-style carousel)</label>
                    <p class="text-xs text-slate-400 mb-2">Add slide image URLs (one per line). These create a swipeable carousel notification.</p>
                    <textarea name="slides" class="form-input font-mono text-xs" rows="3" placeholder="https://example.com/slide1.jpg&#10;https://example.com/slide2.jpg"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Broadcast Now</button>
            </form>
        </div>
    </div>

    {{-- Recent logs --}}
    <div class="card">
        <div class="card-body">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-bold text-slate-800 dark:text-slate-100">Recent Push Logs</h3>
                <a href="{{ route('admin.push-settings.logs') }}" class="text-sm text-blue-600 hover:underline">View all →</a>
            </div>
            @if($recentLogs->isEmpty())
                <p class="text-center text-slate-400 py-4">No push notifications sent yet.</p>
            @else
                <div class="space-y-2">
                    @foreach($recentLogs as $log)
                        <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 dark:bg-slate-700/30 text-sm">
                            <div>
                                <p class="font-semibold text-slate-700 dark:text-slate-200">{{ $log->title ?? 'Notification' }}</p>
                                <p class="text-xs text-slate-400">{{ $log->created_at->diffForHumans() }}</p>
                            </div>
                            <span class="badge @if(($log->status ?? '') === 'sent') badge-success @else badge-warning @endif">{{ $log->status ?? 'unknown' }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
