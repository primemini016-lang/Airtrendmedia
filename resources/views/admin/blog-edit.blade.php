@extends('layouts.admin')
@section('title', 'Edit Blog Post')
@section('heading', 'Edit Blog Post')

@section('content')

<div class="mb-6">
    <a href="{{ route('admin.blog') }}" class="text-sm text-blue-600 hover:underline flex items-center gap-1">
        <x-icon name="arrow-left" class="w-4 h-4" /> Back to Blog
    </a>
</div>

<div class="card p-6">
    <form action="{{ route('admin.blog.update', $post) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
        @csrf @method('PUT')

        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Title</label>
            <input type="text" name="title" value="{{ old('title', $post->title) }}" required class="input w-full">
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Category</label>
                <select name="category_id" class="input w-full">
                    <option value="">— Select —</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ (old('category_id', $post->category_id) == $cat->id) ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Status</label>
                <select name="status" class="input w-full">
                    <option value="draft" {{ old('status', $post->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="published" {{ old('status', $post->status) === 'published' ? 'selected' : '' }}>Published</option>
                    <option value="scheduled" {{ old('status', $post->status) === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Excerpt</label>
            <textarea name="excerpt" rows="2" class="input w-full">{{ old('excerpt', $post->excerpt) }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Content (HTML supported)</label>
            <textarea name="content" required rows="14" class="input w-full font-mono text-sm">{{ old('content', $post->content) }}</textarea>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Tags (comma-separated)</label>
                <input type="text" name="tags" value="{{ old('tags', $post->tags) }}" class="input w-full">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Publish Date</label>
                <input type="datetime-local" name="published_at" value="{{ old('published_at', $post->published_at?->format('Y-m-d\TH:i')) }}" class="input w-full">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Featured Image</label>
            @if($post->featured_image)
                <div class="mb-2">
                    <img src="{{ storage_asset($post->featured_image) }}" class="w-48 h-32 rounded-lg object-cover" alt="Current">
                </div>
            @endif
            <input type="file" name="featured_image" accept="image/*" class="input w-full text-sm">
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Meta Title (SEO)</label>
                <input type="text" name="meta_title" value="{{ old('meta_title', $post->meta_title) }}" class="input w-full">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Meta Description (SEO)</label>
                <input type="text" name="meta_description" value="{{ old('meta_description', $post->meta_description) }}" class="input w-full">
            </div>
        </div>

        <div class="flex items-center gap-2">
            <input type="hidden" name="is_featured" value="0"><input type="checkbox" name="is_featured" id="is_featured" class="rounded" {{ old('is_featured', $post->is_featured) ? 'checked' : '' }}>
            <label for="is_featured" class="text-sm text-slate-700 dark:text-slate-300 flex items-center gap-1">
                <x-icon name="star" class="w-4 h-4 text-yellow-500" /> Featured post
            </label>
        </div>

        {{-- Stats display --}}
        <div class="bg-slate-50 dark:bg-slate-700/30 rounded-xl p-4 grid grid-cols-4 gap-4 text-center">
            <div>
                <p class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ number_format($post->views_count) }}</p>
                <p class="text-xs text-slate-500 flex items-center justify-center gap-1"><x-icon name="view" class="w-3 h-3" /> Views</p>
            </div>
            <div>
                <p class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ $post->likes_count }}</p>
                <p class="text-xs text-slate-500 flex items-center justify-center gap-1"><x-icon name="like" class="w-3 h-3" /> Likes</p>
            </div>
            <div>
                <p class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ $post->comments_count }}</p>
                <p class="text-xs text-slate-500 flex items-center justify-center gap-1"><x-icon name="comment" class="w-3 h-3" /> Comments</p>
            </div>
            <div>
                <p class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ number_format($post->averageRating(), 1) }}</p>
                <p class="text-xs text-slate-500 flex items-center justify-center gap-1"><x-icon name="rate" class="w-3 h-3 text-yellow-500" /> Rating</p>
            </div>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn btn-primary flex-1">Update Post</button>
            <a href="{{ route('admin.blog') }}" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>

@endsection
