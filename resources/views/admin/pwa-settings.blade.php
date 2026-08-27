@extends('layouts.admin')

@section('title', 'PWA Settings')
@section('heading', 'PWA / Mobile App Settings')

@section('content')
<div class="space-y-6">

    <div class="card bg-gradient-to-r from-blue-600 to-indigo-700 text-white">
        <div class="card-body">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
                    <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M17 1.01L7 1c-1.1 0-2 .9-2 2v18c0 1.1.9 2 2 2h10c1.1 0 2-.9 2-2V3c0-1.1-.9-1.99-2-1.99zM17 19H7V5h10v14z"/></svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold">Progressive Web App</h2>
                    <p class="text-blue-100 text-sm mt-1">Configure the installable PWA. Users can add Airtrendmedia to their home screen with native app behavior, offline support, and push notifications.</p>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.pwa-settings.save') }}" method="POST">
        @csrf
        <div class="card">
            <div class="card-body">
                <h3 class="font-bold text-slate-800 dark:text-slate-100 mb-4">General</h3>
                <div class="space-y-4">
                    <label class="flex items-center gap-3">
                        <input type="checkbox" name="pwa_enabled" value="1" class="w-5 h-5 rounded" @if($s->pwa_enabled ?? true) checked @endif>
                        <div>
                            <span class="font-semibold text-slate-700 dark:text-slate-200">Enable PWA</span>
                            <p class="text-xs text-slate-400">Serve manifest.json and register service worker</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3">
                        <input type="checkbox" name="pwa_native_app_behavior" value="1" class="w-5 h-5 rounded" @if($s->pwa_native_app_behavior ?? false) checked @endif>
                        <div>
                            <span class="font-semibold text-slate-700 dark:text-slate-200">Native App Behavior</span>
                            <p class="text-xs text-slate-400">Hide browser UI, full-screen experience</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3">
                        <input type="checkbox" name="pwa_offline_enabled" value="1" class="w-5 h-5 rounded" @if($s->pwa_offline_enabled ?? true) checked @endif>
                        <div>
                            <span class="font-semibold text-slate-700 dark:text-slate-200">Offline Support</span>
                            <p class="text-xs text-slate-400">Cache pages and show offline screen when no connection</p>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h3 class="font-bold text-slate-800 dark:text-slate-100 mb-4">App Identity</h3>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">App Name</label>
                        <input type="text" name="pwa_app_name" class="form-input" value="{{ $s->pwa_app_name ?? 'Airtrendmedia' }}" placeholder="Airtrendmedia">
                    </div>
                    <div>
                        <label class="form-label">Short Name</label>
                        <input type="text" name="pwa_short_name" class="form-input" value="{{ $s->pwa_short_name ?? 'Airtrendmedia' }}" placeholder="Airtrendmedia" maxlength="30">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="form-label">Description</label>
                        <input type="text" name="pwa_description" class="form-input" value="{{ $s->pwa_description ?? '' }}" placeholder="All-in-one social media, micro-jobs & advertising platform">
                    </div>
                    <div>
                        <label class="form-label">Theme Color</label>
                        <div class="flex gap-2">
                            <input type="color" name="pwa_theme_color" class="w-12 h-10 rounded cursor-pointer" value="{{ $s->pwa_theme_color ?? '#1877F2' }}">
                            <input type="text" id="theme-color-text" class="form-input flex-1" value="{{ $s->pwa_theme_color ?? '#1877F2' }}" oninput="document.querySelector('input[name=pwa_theme_color]').value=this.value">
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Background Color</label>
                        <div class="flex gap-2">
                            <input type="color" name="pwa_background_color" class="w-12 h-10 rounded cursor-pointer" value="{{ $s->pwa_background_color ?? '#FFFFFF' }}">
                            <input type="text" class="form-input flex-1" value="{{ $s->pwa_background_color ?? '#FFFFFF' }}" oninput="document.querySelector('input[name=pwa_background_color]').value=this.value">
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Display Mode</label>
                        <select name="pwa_display" class="form-input">
                            <option value="standalone" @selected(($s->pwa_display ?? 'standalone')==='standalone')>Standalone (recommended)</option>
                            <option value="fullscreen" @selected(($s->pwa_display ?? '')==='fullscreen')>Fullscreen</option>
                            <option value="minimal-ui" @selected(($s->pwa_display ?? '')==='minimal-ui')>Minimal UI</option>
                            <option value="browser" @selected(($s->pwa_display ?? '')==='browser')>Browser</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Orientation</label>
                        <select name="pwa_orientation" class="form-input">
                            <option value="any" @selected(($s->pwa_orientation ?? 'any')==='any')>Any</option>
                            <option value="portrait" @selected(($s->pwa_orientation ?? '')==='portrait')>Portrait</option>
                            <option value="landscape" @selected(($s->pwa_orientation ?? '')==='landscape')>Landscape</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h3 class="font-bold text-slate-800 dark:text-slate-100 mb-4">Offline Page</h3>
                <div>
                    <label class="form-label">Offline Message</label>
                    <textarea name="pwa_offline_message" class="form-input" rows="2" placeholder="You are offline. Please check your connection.">{{ $s->pwa_offline_message ?? '' }}</textarea>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h3 class="font-bold text-slate-800 dark:text-slate-100 mb-4">Custom CSS & JS</h3>
                <div class="space-y-4">
                    <div>
                        <label class="form-label">Custom CSS (injected into PWA)</label>
                        <textarea name="pwa_custom_css" class="form-input font-mono text-xs" rows="4" placeholder="/* custom styles */">{{ $s->pwa_custom_css ?? '' }}</textarea>
                    </div>
                    <div>
                        <label class="form-label">Custom JS (injected into PWA)</label>
                        <textarea name="pwa_custom_js" class="form-input font-mono text-xs" rows="4" placeholder="// custom scripts">{{ $s->pwa_custom_js ?? '' }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn btn-primary">Save & Regenerate Manifest</button>
        </div>
    </form>

    <div class="card">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 dark:text-slate-100 mb-2">Current Manifest Preview</h3>
            <p class="text-xs text-slate-400 mb-2">File: <code>public/manifest.json</code> — regenerated on save.</p>
            <a href="{{ asset('manifest.json') }}" target="_blank" class="text-sm text-blue-600 hover:underline">View manifest.json →</a> ·
            <a href="{{ asset('sw.js') }}" target="_blank" class="text-sm text-blue-600 hover:underline">View service worker →</a> ·
            <a href="{{ asset('offline.html') }}" target="_blank" class="text-sm text-blue-600 hover:underline">View offline page →</a>
        </div>
    </div>
</div>
@endsection
