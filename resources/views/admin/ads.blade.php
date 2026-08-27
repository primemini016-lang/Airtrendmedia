@extends('layouts.admin')

@section('title', 'Ads Management')
@section('heading', 'Advertisements')

@section('content')
<div class="mb-6">
    <p class="text-slate-500 text-sm">Place ads anywhere on the website. Choose a position, type (HTML, image, or text), and schedule with start/end dates.</p>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <!-- Create / Form -->
    <div class="card">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 mb-4">Create New Ad</h3>
            <form action="{{ route('admin.ads.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="label">Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" class="input" placeholder="e.g. Summer Promo Banner" required>
                </div>
                <div class="mb-3">
                    <label class="label">Position <span class="text-red-500">*</span></label>
                    <select name="position" class="input" required>
                        <option value="header_top">Header Top (above navbar)</option>
                        <option value="header_bottom">Header Bottom (below navbar)</option>
                        <option value="sidebar_top">Sidebar Top</option>
                        <option value="sidebar_bottom">Sidebar Bottom</option>
                        <option value="content_top">Content Top</option>
                        <option value="content_bottom">Content Bottom</option>
                        <option value="footer_top">Footer Top</option>
                        <option value="footer_bottom">Footer Bottom</option>
                        <option value="home_hero">Home Hero Section</option>
                        <option value="browse_top">Browse Page Top</option>
                        <option value="dashboard_top">Dashboard Top</option>
                        <option value="dashboard_sidebar">Dashboard Sidebar</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="label">Type <span class="text-red-500">*</span></label>
                    <select name="type" class="input" required id="ad-type-select">
                        <option value="html">HTML Code</option>
                        <option value="image">Image</option>
                        <option value="text">Text</option>
                    </select>
                </div>
                <div class="mb-3" id="content-field">
                    <label class="label">Content / HTML</label>
                    <textarea name="content" class="input" rows="4" placeholder="Paste HTML ad code or ad text here..."></textarea>
                </div>
                <div class="mb-3 hidden" id="image-field">
                    <label class="label">Ad Image</label>
                    <input type="file" name="image_file" class="input" accept="image/*">
                </div>
                <div class="mb-3">
                    <label class="label">Link URL</label>
                    <input type="text" name="link_url" class="input" placeholder="https://...">
                </div>
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div><label class="label">Starts At</label><input type="datetime-local" name="starts_at" class="input"></div>
                    <div><label class="label">Ends At</label><input type="datetime-local" name="ends_at" class="input"></div>
                </div>
                <div class="mb-3">
                    <label class="label">Sort Order</label>
                    <input type="number" name="sort_order" class="input" value="0" min="0">
                </div>
                <div class="mb-4 flex items-center gap-2">
                    <input type="checkbox" name="active" value="1" checked class="w-4 h-4">
                    <label class="text-sm text-slate-600">Active (show immediately)</label>
                </div>
                <button class="btn btn-primary w-full">Create Ad</button>
            </form>
        </div>
    </div>

    <!-- List -->
    <div class="card lg:col-span-2">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 mb-4">All Ads ({{ $ads->total() }})</h3>
            @if($ads->isEmpty())
                <p class="text-slate-400 text-sm py-8 text-center">No ads yet. Create one using the form on the left.</p>
            @else
                <div class="space-y-3">
                    @foreach($ads as $ad)
                    <div class="border border-slate-200 rounded-lg p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-semibold text-slate-800">{{ $ad->title }}</span>
                                    <span class="badge badge-info">{{ ucfirst($ad->position) }}</span>
                                    <span class="badge badge-muted">{{ ucfirst($ad->type) }}</span>
                                    @if($ad->active)<span class="badge badge-success">Active</span>@else<span class="badge badge-danger">Inactive</span>@endif
                                </div>
                                @if($ad->link_url)<p class="text-xs text-blue-600 mt-1 truncate">{{ $ad->link_url }}</p>@endif
                                @if($ad->image_path)<img src="{{ Storage::url($ad->image_path) }}" class="mt-2 max-h-20 rounded-lg border border-slate-200" alt="{{ $ad->title }}">@endif
                                @if($ad->content && $ad->type !== 'image')<p class="text-xs text-slate-500 mt-1 line-clamp-2">{{ Str::limit(strip_tags($ad->content), 120) }}</p>@endif
                                <div class="text-xs text-slate-400 mt-2">
                                    @if($ad->starts_at) <span>Start: {{ $ad->starts_at->format('M j, Y') }}</span> @endif
                                    @if($ad->ends_at) <span class="ml-2">End: {{ $ad->ends_at->format('M j, Y') }}</span> @endif
                                </div>
                            </div>
                            <div class="flex gap-1 shrink-0">
                                <form method="POST" action="{{ route('admin.ads.update', $ad) }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="title" value="{{ $ad->title }}">
                                    <input type="hidden" name="position" value="{{ $ad->position }}">
                                    <input type="hidden" name="type" value="{{ $ad->type }}">
                                    <input type="hidden" name="content" value="{{ $ad->content }}">
                                    <input type="hidden" name="link_url" value="{{ $ad->link_url }}">
                                    <input type="hidden" name="sort_order" value="{{ $ad->sort_order }}">
                                    <input type="hidden" name="active" value="{{ $ad->active ? 0 : 1 }}">
                                    <button class="btn btn-outline text-xs">{{ $ad->active ? 'Disable' : 'Enable' }}</button>
                                </form>
                                <form method="POST" action="{{ route('admin.ads.delete', $ad) }}" onsubmit="return confirm('Delete this ad?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger text-xs">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="mt-4">{{ $ads->links() }}</div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('ad-type-select').addEventListener('change', function() {
    var isImage = this.value === 'image';
    document.getElementById('content-field').classList.toggle('hidden', isImage);
    document.getElementById('image-field').classList.toggle('hidden', !isImage);
});
</script>
@endpush
@endsection
