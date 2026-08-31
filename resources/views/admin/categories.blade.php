@extends('layouts.admin')

@section('title', 'Categories')
@section('heading', 'Task Categories')

@section('content')
<div class="grid lg:grid-cols-3 gap-6">
    <!-- Create form -->
    <div class="card">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 mb-4">Add Category</h3>
            <form action="{{ route('admin.categories') }}" method="POST">
                @csrf
                <div class="mb-3"><label class="label">Name <span class="text-red-500">*</span></label><input type="text" name="name" class="input" required></div>
                <div class="mb-3"><label class="label">Parent (optional)</label>
                    <select name="parent_id" class="input"><option value="">None (top-level)</option>
                        @foreach($all as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div>
                        <label class="label">Icon (emoji)</label>
                        <select name="icon" class="input">
                            <option value="briefcase">General</option>
                            <option value="social_platform">Social platform</option>
                            <option value="twitter">Twitter / X</option>
                            <option value="instagram">Instagram</option>
                            <option value="youtube">YouTube</option>
                            <option value="tiktok">TikTok</option>
                            <option value="linkedin">LinkedIn</option>
                            <option value="telegram">Telegram</option>
                            <option value="whatsapp">WhatsApp</option>
                            <option value="writing">Writing</option>
                            <option value="design">Design</option>
                            <option value="marketing">Marketing</option>
                            <option value="music">Music</option>
                            <option value="video">Video</option>
                            <option value="tech">Technology</option>
                        </select>
                    </div>
                    <div>
                        <label class="label">Color</label>
                        <input type="color" name="color" class="input h-10 cursor-pointer" value="#2563eb">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div><label class="label">Default Price</label><input type="number" name="price" class="input" step="0.01" value="0"></div>
                    <div><label class="label">Min Workers</label><input type="number" name="min_amount" class="input" value="1"></div>
                </div>
                <div class="mb-3"><label class="label">Position</label><input type="number" name="position" class="input" value="0"></div>
                <div class="flex items-center mb-4"><input type="checkbox" name="active" value="1" checked class="rounded border-slate-300 text-blue-600 mr-2"><label class="text-sm text-slate-600">Active</label></div>
                <button class="btn btn-primary w-full">Create Category</button>
            </form>
        </div>
    </div>

    <!-- List -->
    <div class="card lg:col-span-2">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 mb-4">Categories ({{ $all->count() }})</h3>
            @if($categories->isEmpty())<p class="text-slate-400 text-center py-8">No categories yet.</p>
            @else
                <div class="space-y-4">
                    @foreach($categories as $cat)
                        <div class="border border-slate-200 rounded-lg p-3">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center justify-center">@categoryIcon3D($cat, 36)</span>
                                    <span class="font-semibold text-slate-800">{{ $cat->name }}</span>
                                    @if($cat->active)<span class="badge badge-success">Active</span>@else<span class="badge badge-muted">Inactive</span>@endif
                                </div>
                                <div class="flex gap-2">
                                    <form action="{{ route('admin.categories.update', $cat) }}" method="POST">@csrf
                                        <input type="hidden" name="name" value="{{ $cat->name }}">
                                        <input type="hidden" name="price" value="{{ $cat->price }}">
                                        <input type="hidden" name="min_amount" value="{{ $cat->min_amount }}">
                                        <input type="hidden" name="position" value="{{ $cat->position }}">
                                        <input type="hidden" name="active" value="{{ $cat->active ? 1 : 0 }}">
                                        <button class="text-amber-600 hover:underline text-xs" onclick="event.preventDefault();toggleCat(this, {{ $cat->id }})">{{ $cat->active ? 'Deactivate' : 'Activate' }}</button>
                                    </form>
                                    <form action="{{ route('admin.categories.delete', $cat) }}" method="POST" class="inline">@csrf @method('DELETE')<button class="text-red-600 hover:underline text-xs" onclick="return confirm('Delete this category?')">Delete</button></form>
                                </div>
                            </div>
                            <details class="mt-3">
                                <summary class="cursor-pointer text-sm font-semibold text-blue-600">Edit category, icon & settings</summary>
                                <form action="{{ route('admin.categories.update', $cat) }}" method="POST" class="mt-3 grid sm:grid-cols-2 gap-3">
                                    @csrf
                                    <div><label class="label">Name</label><input name="name" value="{{ $cat->name }}" required class="input"></div>
                                    <div><label class="label">Icon</label>
                                        <select name="icon" class="input">
                                            @foreach(['briefcase'=>'General','social_platform'=>'Social Platform','twitter'=>'Twitter / X','instagram'=>'Instagram','youtube'=>'YouTube','tiktok'=>'TikTok','linkedin'=>'LinkedIn','telegram'=>'Telegram','whatsapp'=>'WhatsApp','writing'=>'Writing','design'=>'Design','marketing'=>'Marketing','music'=>'Music','video'=>'Video','tech'=>'Technology'] as $iconKey=>$iconLabel)
                                                <option value="{{ $iconKey }}" @selected(($cat->icon ?: 'briefcase') === $iconKey)>{{ $iconLabel }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div><label class="label">Color</label><input type="color" name="color" value="{{ $cat->color ?: '#2563eb' }}" class="input h-10"></div>
                                    <div><label class="label">Position</label><input type="number" name="position" value="{{ $cat->position }}" min="0" class="input"></div>
                                    <div><label class="label">Default Price</label><input type="number" step="0.01" min="0" name="price" value="{{ $cat->price }}" class="input"></div>
                                    <div><label class="label">Minimum Workers</label><input type="number" min="1" name="min_amount" value="{{ $cat->min_amount }}" class="input"></div>
                                    <div class="sm:col-span-2 flex items-center gap-2">
                                        <input type="hidden" name="active" value="0">
                                        <input type="checkbox" name="active" value="1" @checked($cat->active) class="rounded border-slate-300 text-blue-600">
                                        <span class="text-sm text-slate-600">Category active</span>
                                    </div>
                                    <button class="btn btn-primary sm:col-span-2">Save Category</button>
                                </form>
                            </details>
                            <div class="text-xs text-slate-400 mt-2">Price: {{ money((float)$cat->price) }} · Min: {{ $cat->min_amount }} · Pos: {{ $cat->position }}</div>
                            @if($cat->children->isNotEmpty())
                                <div class="mt-2 pl-4 border-l-2 border-slate-100 space-y-1">
                                    @foreach($cat->children as $child)
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-slate-600">{{ $child->name }}</span>
                                            <form action="{{ route('admin.categories.delete', $child) }}" method="POST">@csrf @method('DELETE')<button class="text-red-600 hover:underline text-xs" onclick="return confirm('Delete?')">Delete</button></form>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleCat(btn, id){
    // toggling active is handled by resubmitting the update form with flipped active value
    const form = btn.closest('form');
    const inp = form.querySelector('input[name=active]');
    inp.value = inp.value === '1' ? '0' : '1';
    form.submit();
}
</script>
@endpush
@endsection
