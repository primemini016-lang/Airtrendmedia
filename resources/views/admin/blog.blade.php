@extends('layouts.admin')
@section('title', 'Blog Management')
@section('heading', 'Blog Management')

@section('content')

<div class="mb-6 flex items-center justify-between flex-wrap gap-4">
    <div>
        <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Blog Posts</h2>
        <p class="text-sm text-slate-500 mt-1">Manage blog articles, categories, and publishing.</p>
    </div>
    <button onclick="document.getElementById('create-modal').classList.remove('hidden')"
            class="btn btn-primary flex items-center gap-2">
        <x-icon name="plus" class="w-5 h-5" /> New Blog Post
    </button>
</div>

{{-- Stats row --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="card p-4">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg text-blue-600"><x-icon name="blog" class="w-5 h-5" /></div>
            <div>
                <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 stat-value">{{ $posts->total() }}</p>
                <p class="text-xs text-slate-500">Total Posts</p>
            </div>
        </div>
    </div>
    <div class="card p-4">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg text-green-600"><x-icon name="check" class="w-5 h-5" /></div>
            <div>
                <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 stat-value">{{ $posts->where('status', 'published')->count() }}</p>
                <p class="text-xs text-slate-500">Published</p>
            </div>
        </div>
    </div>
    <div class="card p-4">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg text-yellow-600"><x-icon name="clock" class="w-5 h-5" /></div>
            <div>
                <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 stat-value">{{ $posts->where('status', 'draft')->count() }}</p>
                <p class="text-xs text-slate-500">Drafts</p>
            </div>
        </div>
    </div>
    <div class="card p-4">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg text-purple-600"><x-icon name="categories" class="w-5 h-5" /></div>
            <div>
                <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 stat-value">{{ $categories->count() }}</p>
                <p class="text-xs text-slate-500">Categories</p>
            </div>
        </div>
    </div>
</div>

{{-- Blog posts table --}}
<div class="card overflow-hidden mb-8">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-700/50 text-slate-600 dark:text-slate-300">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold">Post</th>
                    <th class="text-left px-4 py-3 font-semibold">Category</th>
                    <th class="text-left px-4 py-3 font-semibold">Status</th>
                    <th class="text-center px-4 py-3 font-semibold">Stats</th>
                    <th class="text-left px-4 py-3 font-semibold">Date</th>
                    <th class="text-right px-4 py-3 font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @foreach($posts as $post)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            @if($post->featured_image)
                                <img src="{{ Storage::url($post->featured_image) }}" class="w-12 h-12 rounded-lg object-cover" alt="">
                            @else
                                <div class="w-12 h-12 rounded-lg bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-slate-400">
                                    <x-icon name="image" class="w-5 h-5" />
                                </div>
                            @endif
                            <div class="min-w-0">
                                <a href="{{ route('blog.show', $post->slug) }}" target="_blank" class="font-medium text-slate-800 dark:text-slate-100 hover:text-blue-600 line-clamp-1">{{ $post->title }}</a>
                                @if($post->is_featured)
                                    <span class="text-xs text-yellow-500 flex items-center gap-0.5"><x-icon name="star" class="w-3 h-3" /> Featured</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $post->category?->name ?? '—' }}</td>
                    <td class="px-4 py-3">
                        @if($post->status === 'published')
                            <span class="badge bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Published</span>
                        @elseif($post->status === 'draft')
                            <span class="badge bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400">Draft</span>
                        @else
                            <span class="badge bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">Scheduled</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-center gap-3 text-xs text-slate-500">
                            <span class="flex items-center gap-1" title="Views"><x-icon name="view" class="w-3.5 h-3.5" />{{ number_format($post->views_count) }}</span>
                            <span class="flex items-center gap-1" title="Likes"><x-icon name="like" class="w-3.5 h-3.5" />{{ $post->likes_count }}</span>
                            <span class="flex items-center gap-1" title="Comments"><x-icon name="comment" class="w-3.5 h-3.5" />{{ $post->comments_count }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-slate-500 text-xs">{{ $post->published_at?->format('M j, Y') ?? $post->created_at->format('M j, Y') }}</td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.blog.edit', $post) }}" class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30" title="Edit">
                                <x-icon name="edit" class="w-4 h-4" />
                            </a>
                            <form action="{{ route('admin.blog.delete', $post) }}" method="POST" onsubmit="return confirm('Delete this post?')">
                                @csrf @method('DELETE')
                                <button class="p-1.5 rounded-lg text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30" title="Delete">
                                    <x-icon name="trash" class="w-4 h-4" />
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
                @if($posts->isEmpty())
                <tr>
                    <td colspan="6" class="px-4 py-12 text-center text-slate-400">
                        <x-icon name="blog" class="w-10 h-10 mx-auto mb-2 opacity-50" />
                        <p>No blog posts yet. Click "New Blog Post" to create one.</p>
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

{{ $posts->links() }}

{{-- Categories section --}}
<div class="card p-6 mb-8">
    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center gap-2">
        <x-icon name="categories" class="w-5 h-5" /> Blog Categories
    </h3>
    <form action="{{ route('admin.blog.category.store') }}" method="POST" class="flex gap-2 mb-4">
        @csrf
        <input type="text" name="name" placeholder="Category name" required class="input flex-1">
        <button type="submit" class="btn btn-primary flex items-center gap-1"><x-icon name="plus" class="w-4 h-4" /> Add</button>
    </form>
    <div class="flex flex-wrap gap-2">
        @foreach($categories as $cat)
            <div class="flex items-center gap-2 px-3 py-1.5 bg-slate-100 dark:bg-slate-700 rounded-full text-sm">
                <span>{{ $cat->name }}</span>
                <span class="text-xs text-slate-400">({{ $cat->posts_count }})</span>
                <form action="{{ route('admin.blog.category.delete', $cat) }}" method="POST" onsubmit="return confirm('Delete this category?')">
                    @csrf @method('DELETE')
                    <button class="text-red-500 hover:text-red-700"><x-icon name="x" class="w-3 h-3" /></button>
                </form>
            </div>
        @endforeach
        @if($categories->isEmpty())
            <p class="text-sm text-slate-400">No categories yet.</p>
        @endif
    </div>
</div>

{{-- Create modal --}}
<div id="create-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100">Create Blog Post</h3>
                <button onclick="document.getElementById('create-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600"><x-icon name="x" class="w-5 h-5" /></button>
            </div>
            <form action="{{ route('admin.blog.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Title</label>
                    <input type="text" name="title" required class="input w-full" placeholder="Enter post title">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Category</label>
                        <select name="category_id" class="input w-full">
                            <option value="">— Select —</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Status</label>
                        <select name="status" class="input w-full">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                            <option value="scheduled">Scheduled</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Excerpt (optional)</label>
                    <textarea name="excerpt" rows="2" class="input w-full" placeholder="Short summary..."></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Content</label>
                    <textarea name="content" required rows="8" class="input w-full font-mono text-sm" placeholder="Write your blog post content (HTML supported)..."></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Featured Image</label>
                        <input type="file" name="featured_image" accept="image/*" class="input w-full text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Tags (comma-separated)</label>
                        <input type="text" name="tags" class="input w-full" placeholder="freelance, tips, money">
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_featured" id="is_featured" class="rounded">
                    <label for="is_featured" class="text-sm text-slate-700 dark:text-slate-300">Featured post</label>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="btn btn-primary flex-1">Create Post</button>
                    <button type="button" onclick="document.getElementById('create-modal').classList.add('hidden')" class="btn btn-outline">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
