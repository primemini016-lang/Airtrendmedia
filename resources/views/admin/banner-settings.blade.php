@extends('layouts.admin')

@section('title', 'Banner & Popup Settings')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Banner & Popup Settings</h1>
        <p class="text-sm text-slate-500 mt-1">Manage header banners, footer banners, popup banners, and PWA install popups. All settings are admin-controlled and appear on the frontend.</p>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200 text-green-700 dark:bg-green-900/30 dark:border-green-800 dark:text-green-300">{{ session('success') }}</div>
    @endif

    <form action="{{ route('admin.banner-settings.save') }}" method="POST">
        @csrf

        {{-- ===== Header Banner ===== --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 mb-6">
            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h18v6H3V3zm0 10h18v8H3v-8z"/></svg>
                Header Banner
            </h2>
            <p class="text-sm text-slate-500 mb-4">A thin announcement banner displayed at the very top of every page (above the header).</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="header_banner_enabled" value="1" @if($s->header_banner_enabled) checked @endif class="w-5 h-5 rounded text-brand-600">
                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Enable header banner</span>
                </label>
                <div>
                    <label class="block text-sm font-medium text-slate-600 dark:text-slate-400 mb-1">Link URL (optional)</label>
                    <input type="text" name="header_banner_link" value="{{ old('header_banner_link', $s->header_banner_link) }}" class="form-input w-full" placeholder="https://example.com/promo">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-600 dark:text-slate-400 mb-1">Banner text</label>
                    <textarea name="header_banner_text" rows="2" class="form-input w-full" placeholder="🎉 Special offer! Get 20% off on all gigs this week.">{{ old('header_banner_text', $s->header_banner_text) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 dark:text-slate-400 mb-1">Background color</label>
                    <input type="text" name="header_banner_bg_color" value="{{ old('header_banner_bg_color', $s->header_banner_bg_color ?? '#1877F2') }}" class="form-input w-full" placeholder="#1877F2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 dark:text-slate-400 mb-1">Text color</label>
                    <input type="text" name="header_banner_text_color" value="{{ old('header_banner_text_color', $s->header_banner_text_color ?? '#FFFFFF') }}" class="form-input w-full" placeholder="#FFFFFF">
                </div>
            </div>
        </div>

        {{-- ===== Footer Banner ===== --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 mb-6">
            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12h18M3 6h18M3 18h18"/></svg>
                Footer Banner
            </h2>
            <p class="text-sm text-slate-500 mb-4">A banner displayed above the footer on every page.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="footer_banner_enabled" value="1" @if($s->footer_banner_enabled) checked @endif class="w-5 h-5 rounded text-brand-600">
                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Enable footer banner</span>
                </label>
                <div>
                    <label class="block text-sm font-medium text-slate-600 dark:text-slate-400 mb-1">Link URL (optional)</label>
                    <input type="text" name="footer_banner_link" value="{{ old('footer_banner_link', $s->footer_banner_link) }}" class="form-input w-full" placeholder="https://example.com">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-600 dark:text-slate-400 mb-1">Banner text</label>
                    <textarea name="footer_banner_text" rows="2" class="form-input w-full" placeholder="Trusted by 10,000+ users worldwide. Join Airtrendmedia today!">{{ old('footer_banner_text', $s->footer_banner_text) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 dark:text-slate-400 mb-1">Background color</label>
                    <input type="text" name="footer_banner_bg_color" value="{{ old('footer_banner_bg_color', $s->footer_banner_bg_color ?? '#F0F2F5') }}" class="form-input w-full" placeholder="#F0F2F5">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 dark:text-slate-400 mb-1">Text color</label>
                    <input type="text" name="footer_banner_text_color" value="{{ old('footer_banner_text_color', $s->footer_banner_text_color ?? '#1877F2') }}" class="form-input w-full" placeholder="#1877F2">
                </div>
            </div>
        </div>

        {{-- ===== Popup Banner ===== --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 mb-6">
            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Popup Banner
            </h2>
            <p class="text-sm text-slate-500 mb-4">A modal popup that appears on the frontend. You can set the description text, image, link, timing, and how often it reappears.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="popup_banner_enabled" value="1" @if($s->popup_banner_enabled) checked @endif class="w-5 h-5 rounded text-brand-600">
                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Enable popup banner</span>
                </label>
                <div>
                    <label class="block text-sm font-medium text-slate-600 dark:text-slate-400 mb-1">Background color</label>
                    <input type="text" name="popup_banner_bg_color" value="{{ old('popup_banner_bg_color', $s->popup_banner_bg_color ?? '#FFFFFF') }}" class="form-input w-full" placeholder="#FFFFFF">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 dark:text-slate-400 mb-1">Popup title</label>
                    <input type="text" name="popup_banner_title" value="{{ old('popup_banner_title', $s->popup_banner_title) }}" class="form-input w-full" placeholder="Welcome to Airtrendmedia!">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 dark:text-slate-400 mb-1">Image URL (optional)</label>
                    <input type="text" name="popup_banner_image" value="{{ old('popup_banner_image', $s->popup_banner_image) }}" class="form-input w-full" placeholder="https://example.com/banner.jpg">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-600 dark:text-slate-400 mb-1">Description text</label>
                    <textarea name="popup_banner_description" rows="4" class="form-input w-full" placeholder="Enter the popup banner description text here. This is shown to users when the popup appears.">{{ old('popup_banner_description', $s->popup_banner_description) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 dark:text-slate-400 mb-1">Link URL (optional)</label>
                    <input type="text" name="popup_banner_link" value="{{ old('popup_banner_link', $s->popup_banner_link) }}" class="form-input w-full" placeholder="https://example.com/offer">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 dark:text-slate-400 mb-1">Link button text</label>
                    <input type="text" name="popup_banner_link_text" value="{{ old('popup_banner_link_text', $s->popup_banner_link_text ?? 'Learn More') }}" class="form-input w-full" placeholder="Learn More">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 dark:text-slate-400 mb-1">Delay before popup (seconds)</label>
                    <input type="number" name="popup_banner_delay_seconds" value="{{ old('popup_banner_delay_seconds', $s->popup_banner_delay_seconds ?? 5) }}" class="form-input w-full" min="0" max="3600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 dark:text-slate-400 mb-1">Show again after (hours)</label>
                    <input type="number" name="popup_banner_show_again_hours" value="{{ old('popup_banner_show_again_hours', $s->popup_banner_show_again_hours ?? 24) }}" class="form-input w-full" min="0" max="720">
                    <p class="text-xs text-slate-400 mt-1">Set to 0 to show every page load. 24 = once per day.</p>
                </div>
            </div>
        </div>

        {{-- ===== PWA Install Popup ===== --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 mb-6">
            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                PWA Install Popup
            </h2>
            <p class="text-sm text-slate-500 mb-4">A popup that encourages users to install the Airtrendmedia PWA app on their device.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="flex items-center gap-3 cursor-pointer md:col-span-2">
                    <input type="checkbox" name="pwa_popup_enabled" value="1" @if($s->pwa_popup_enabled) checked @endif class="w-5 h-5 rounded text-brand-600">
                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Enable PWA install popup</span>
                </label>
                <div>
                    <label class="block text-sm font-medium text-slate-600 dark:text-slate-400 mb-1">Popup title</label>
                    <input type="text" name="pwa_popup_title" value="{{ old('pwa_popup_title', $s->pwa_popup_title) }}" class="form-input w-full" placeholder="Install Airtrendmedia App">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 dark:text-slate-400 mb-1">Install button text</label>
                    <input type="text" name="pwa_popup_install_btn_text" value="{{ old('pwa_popup_install_btn_text', $s->pwa_popup_install_btn_text ?? 'Install Now') }}" class="form-input w-full" placeholder="Install Now">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-600 dark:text-slate-400 mb-1">Description text</label>
                    <textarea name="pwa_popup_description" rows="3" class="form-input w-full" placeholder="Install our app for a faster, offline-capable experience with push notifications.">{{ old('pwa_popup_description', $s->pwa_popup_description) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 dark:text-slate-400 mb-1">Dismiss button text</label>
                    <input type="text" name="pwa_popup_dismiss_btn_text" value="{{ old('pwa_popup_dismiss_btn_text', $s->pwa_popup_dismiss_btn_text ?? 'Maybe Later') }}" class="form-input w-full" placeholder="Maybe Later">
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-6 py-3 bg-brand-600 text-white rounded-lg font-semibold hover:bg-brand-700 transition shadow-sm">
                Save Banner & Popup Settings
            </button>
        </div>
    </form>
</div>
@endsection
