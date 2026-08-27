<?php

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BlogController extends Controller
{
    public function index()
    {
        $user = auth('web')->user();
        $posts = BlogPost::where('author_id', $user->id)->with('category')->latest()->paginate(12);
        return view('user.blog.index', compact('posts'));
    }

    public function create()
    {
        $categories = BlogCategory::where('is_active', true)->orderBy('name')->get();
        return view('user.blog.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $user = auth('web')->user();
        $validated = $request->validate([
            'title' => 'required|string|max:191',
            'content' => 'required|string|max:100000',
            'excerpt' => 'nullable|string|max:500',
            'category_id' => 'nullable|exists:blog_categories,id',
            'featured_image' => 'nullable|image|max:5120',
            'tags' => 'nullable|string|max:500',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'status' => 'required|in:draft,published',
        ]);

        $post = new BlogPost($validated);
        $post->author_id = $user->id;
        $post->published_at = $validated['status'] === 'published' ? now() : null;
        if ($request->hasFile('featured_image')) {
            $post->featured_image = $request->file('featured_image')->store('blog', 'public');
        }
        $post->save();

        return redirect()->route('user.blog.index')->with('success', $post->status === 'published' ? 'Your article is live.' : 'Your article was saved as a draft.');
    }

    public function edit(BlogPost $post)
    {
        abort_unless($post->author_id === auth('web')->id(), 403);
        $categories = BlogCategory::where('is_active', true)->orderBy('name')->get();
        return view('user.blog.edit', compact('post', 'categories'));
    }

    public function update(Request $request, BlogPost $post)
    {
        abort_unless($post->author_id === auth('web')->id(), 403);
        $validated = $request->validate([
            'title' => 'required|string|max:191',
            'content' => 'required|string|max:100000',
            'excerpt' => 'nullable|string|max:500',
            'category_id' => 'nullable|exists:blog_categories,id',
            'featured_image' => 'nullable|image|max:5120',
            'tags' => 'nullable|string|max:500',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'status' => 'required|in:draft,published',
        ]);

        $post->fill($validated);
        if ($validated['status'] === 'published' && !$post->published_at) $post->published_at = now();
        if ($validated['status'] === 'draft') $post->published_at = null;
        if ($request->hasFile('featured_image')) {
            if ($post->featured_image) Storage::disk('public')->delete($post->featured_image);
            $post->featured_image = $request->file('featured_image')->store('blog', 'public');
        }
        $post->save();
        return redirect()->route('user.blog.index')->with('success', 'Article updated successfully.');
    }

    public function destroy(BlogPost $post)
    {
        abort_unless($post->author_id === auth('web')->id(), 403);
        if ($post->featured_image) Storage::disk('public')->delete($post->featured_image);
        $post->delete();
        return back()->with('success', 'Article deleted.');
    }
}
