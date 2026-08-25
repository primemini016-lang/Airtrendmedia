@extends('layouts.admin')

@section('title', 'Appearance')
@section('heading', 'Appearance & Custom Code')

@section('content')
<div class="mb-6">
    <p class="text-slate-500 text-sm">Manage your logo, favicon, universal CSS, and inject custom HTML into header, footer, and body areas.</p>
</div>

<!-- Logo & Favicon -->
<div class="grid sm:grid-cols-2 gap-6 mb-6">
    <div class="card">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 mb-4">Logo</h3>
            @if($s->logo)
                <img src="{{ Storage::url($s->logo) }}" class="max-h-20 mb-3 rounded-lg border border-slate-200 p-2" alt="Logo">
            @else
                <div class="w-9 h-9 rounded-lg auth-gradient flex items-center justify-center text-white font-bold mb-3">M</div>
            @endif
            <form action="{{ route('admin.appearance.logo') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="file" name="logo" class="input mb-3" accept="image/png,jpg,jpeg,svg,webp" required>
                <button class="btn btn-primary">Upload Logo</button>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 mb-4">Favicon</h3>
            @if($s->favicon)
                <img src="{{ Storage::url($s->favicon) }}" class="w-12 h-12 mb-3 rounded-lg border border-slate-200 p-1" alt="Favicon">
            @else
                <div class="w-12 h-12 rounded-lg auth-gradient flex items-center justify-center text-white font-bold mb-3">M</div>
            @endif
            <form action="{{ route('admin.appearance.favicon') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="file" name="favicon" class="input mb-3" accept="image/png,jpg,jpeg,ico,svg,webp" required>
                <button class="btn btn-primary">Upload Favicon</button>
            </form>
        </div>
    </div>
</div>

<!-- Theme colors + Custom Code -->
<form action="{{ route('admin.appearance.update') }}" method="POST">
    @csrf
    <div class="grid lg:grid-cols-2 gap-6 mb-6">
        <div class="card">
            <div class="card-body">
                <h3 class="font-bold text-slate-800 mb-4">Theme Colors</h3>
                <div class="mb-3">
                    <label class="label">Primary Color</label>
                    <div class="flex gap-2">
                        <input type="color" name="primary_color" value="{{ $s->primary_color ?? '#2563eb' }}" class="w-12 h-10 rounded cursor-pointer border border-slate-200">
                        <input type="text" name="primary_color" value="{{ $s->primary_color ?? '#2563eb' }}" class="input" placeholder="#2563eb">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="label">Accent Color</label>
                    <div class="flex gap-2">
                        <input type="color" name="accent_color" value="{{ $s->accent_color ?? '#1e40af' }}" class="w-12 h-10 rounded cursor-pointer border border-slate-200">
                        <input type="text" name="accent_color" value="{{ $s->accent_color ?? '#1e40af' }}" class="input" placeholder="#1e40af">
                    </div>
                </div>
                <p class="text-xs text-slate-400">These colors apply to buttons, links, and accents across the site.</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h3 class="font-bold text-slate-800 mb-4">Universal CSS</h3>
                <textarea name="custom_css" class="input font-mono text-xs" rows="8" placeholder="/* Add custom CSS that applies globally */">{{ $customCss }}</textarea>
                <p class="text-xs text-slate-400 mt-2">This CSS is injected into every page's &lt;head&gt;.</p>
            </div>
        </div>
    </div>

    <h3 class="font-bold text-slate-800 mb-3">HTML Injection</h3>
    <div class="grid lg:grid-cols-2 gap-6 mb-6">
        <div class="card">
            <div class="card-body">
                <label class="label mb-2">Header HTML (inside &lt;head&gt;)</label>
                <textarea name="header_html" class="input font-mono text-xs" rows="6" placeholder="<!-- e.g. Google Analytics, meta tags -->">{{ $headerHtml }}</textarea>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <label class="label mb-2">Footer HTML (before &lt;/body&gt;)</label>
                <textarea name="footer_html" class="input font-mono text-xs" rows="6" placeholder="<!-- e.g. chat widgets, scripts -->">{{ $footerHtml }}</textarea>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <label class="label mb-2">Body Top HTML (right after &lt;body&gt;)</label>
                <textarea name="body_top_html" class="input font-mono text-xs" rows="6" placeholder="<!-- e.g. top banners -->">{{ $bodyTopHtml }}</textarea>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <label class="label mb-2">Body Bottom HTML (before footer)</label>
                <textarea name="body_bottom_html" class="input font-mono text-xs" rows="6" placeholder="<!-- e.g. popups, modals -->">{{ $bodyBottomHtml }}</textarea>
            </div>
        </div>
        <div class="card lg:col-span-2">
            <div class="card-body">
                <label class="label mb-2">Sidebar HTML</label>
                <textarea name="sidebar_html" class="input font-mono text-xs" rows="4" placeholder="<!-- Custom sidebar content -->">{{ $sidebarHtml }}</textarea>
            </div>
        </div>
    </div>

    <button class="btn btn-primary">Save All Appearance Settings</button>
</form>
@endsection
