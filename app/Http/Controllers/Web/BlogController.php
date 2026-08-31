<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\MediaUploadService;
use App\Models\BlogPost;
use App\Models\BlogCategory;
use App\Models\BlogComment;
use App\Models\BlogLike;
use App\Models\BlogView;
use App\Models\BlogRate;
use App\Models\BlogShare;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;

class BlogController extends Controller
{
    /* ========================================================================
       PUBLIC BLOG — Phoenix-style full-screen blog with search
       ======================================================================== */

    /**
     * Blog index — full-screen Phoenix-style listing with search.
     */
    public function index(Request $request)
    {
        $query = BlogPost::published()->with(['category', 'author']);

        // Search functionality
        if ($request->filled('q')) {
            $search = $request->get('q');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('excerpt', 'LIKE', "%{$search}%")
                  ->orWhere('content', 'LIKE', "%{$search}%")
                  ->orWhere('tags', 'LIKE', "%{$search}%");
            });
        }

        // Category filter
        if ($request->filled('category')) {
            $cat = BlogCategory::where('slug', $request->get('category'))->first();
            if ($cat) {
                $query->where('category_id', $cat->id);
            }
        }

        // Sort
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'popular':
                $query->orderBy('views_count', 'desc');
                break;
            case 'liked':
                $query->orderBy('likes_count', 'desc');
                break;
            case 'rated':
                $query->withCount('ratings')->orderBy('ratings_count', 'desc');
                break;
            default:
                $query->latest('published_at');
        }

        $posts = $query->withCount('ratings')->paginate(12);
        $categories = BlogCategory::withCount('publishedPosts')->orderBy('name')->get();
        $featured = BlogPost::published()->featured()->latest('published_at')->take(3)->get();
        $trending = BlogPost::published()->orderBy('views_count', 'desc')->take(5)->get();

        return view('blog.index', compact('posts', 'categories', 'featured', 'trending'));
    }

    /**
     * Show a single blog post — with view tracking, comments, likes, ratings.
     */
    public function show(Request $request, $slug)
    {
        $post = BlogPost::where('slug', $slug)->published()->with([
            'category', 'author',
            'comments' => function ($q) { $q->whereNull('parent_id')->with(['user', 'replies.user'])->latest(); },
        ])->firstOrFail();

        // Track view (unique per user/IP per day)
        $this->trackView($post, $request);

        $related = BlogPost::published()
            ->where('id', '!=', $post->id)
            ->where('category_id', $post->category_id)
            ->latest('published_at')
            ->take(10)
            ->get();

        $user = auth('web')->user();
        $hasLiked = $user ? $post->isLikedBy($user) : false;
        $userRating = $user ? $post->isRatedBy($user) : null;
        $avgRating = $post->averageRating();

        return view('blog.show', compact('post', 'related', 'hasLiked', 'userRating', 'avgRating'));
    }

    /**
     * Like / unlike a blog post.
     */
    public function toggleLike(Request $request, $slug)
    {
        $post = BlogPost::where('slug', $slug)->firstOrFail();
        $user = auth('web')->user();

        if (!$user) {
            return response()->json(['error' => 'Please login to like posts.'], 401);
        }

        $existing = BlogLike::where('post_id', $post->id)->where('user_id', $user->id)->first();
        if ($existing) {
            $existing->delete();
            $post->decrement('likes_count');
            $liked = false;
        } else {
            BlogLike::create(['post_id' => $post->id, 'user_id' => $user->id]);
            $post->increment('likes_count');
            $liked = true;

            // Notify post author
            if ($post->author_id !== $user->id) {
                Notification::create([
                    'user_id' => $post->author_id,
                    'type' => 'blog_like',
                    'title' => 'New like on your blog post',
                    'body' => $user->name . ' liked your post "' . Str::limit($post->title, 50) . '"',
                    'url' => route('blog.show', $post->slug),
                ]);
            }
        }

        return response()->json([
            'liked' => $liked,
            'count' => $post->fresh()->likes_count,
        ]);
    }

    /**
     * Rate a blog post (1-5 stars).
     */
    public function rate(Request $request, $slug)
    {
        $request->validate(['rating' => 'required|integer|min:1|max:5']);
        $post = BlogPost::where('slug', $slug)->firstOrFail();
        $user = auth('web')->user();

        if (!$user) {
            return response()->json(['error' => 'Please login to rate posts.'], 401);
        }

        $existing = BlogRate::where('post_id', $post->id)->where('user_id', $user->id)->first();
        if ($existing) {
            $existing->update(['rating' => $request->rating]);
        } else {
            BlogRate::create([
                'post_id' => $post->id,
                'user_id' => $user->id,
                'rating' => $request->rating,
            ]);
            $post->increment('ratings_count');
        }

        return response()->json([
            'avg' => $post->fresh()->averageRating(),
            'count' => $post->fresh()->ratings_count,
        ]);
    }

    /**
     * Add a comment to a blog post.
     */
    public function comment(Request $request, $slug)
    {
        $request->validate([
            'body' => 'required|string|max:2000',
            'parent_id' => 'nullable|exists:blog_comments,id',
        ]);

        $post = BlogPost::where('slug', $slug)->firstOrFail();
        $user = auth('web')->user();

        if (!$user) {
            return back()->with('error', 'Please login to comment.');
        }

        $comment = BlogComment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'parent_id' => $request->parent_id,
            'body' => $request->body,
        ]);

        $post->increment('comments_count');

        // Notify post author
        if ($post->author_id !== $user->id) {
            Notification::create([
                'user_id' => $post->author_id,
                'type' => 'blog_comment',
                'title' => 'New comment on your blog post',
                'body' => $user->name . ' commented on "' . Str::limit($post->title, 50) . '"',
                'url' => route('blog.show', $post->slug) . '#comment-' . $comment->id,
            ]);
        }

        // Notify parent comment author if reply
        if ($request->parent_id) {
            $parent = BlogComment::find($request->parent_id);
            if ($parent && $parent->user_id !== $user->id) {
                Notification::create([
                    'user_id' => $parent->user_id,
                    'type' => 'blog_reply',
                    'title' => 'New reply to your comment',
                    'body' => $user->name . ' replied to your comment on "' . Str::limit($post->title, 50) . '"',
                    'url' => route('blog.show', $post->slug) . '#comment-' . $comment->id,
                ]);
            }
        }

        return back()->with('success', 'Comment posted successfully!');
    }

    /**
     * Share a blog post (track the share).
     */
    public function share(Request $request, $slug)
    {
        $post = BlogPost::where('slug', $slug)->firstOrFail();
        $platform = $request->get('platform', 'link');

        BlogShare::create([
            'post_id' => $post->id,
            'user_id' => auth('web')->id(),
            'platform' => $platform,
        ]);

        $post->increment('shares_count');

        return response()->json(['count' => $post->fresh()->shares_count]);
    }

    /**
     * Track a unique view for the blog post.
     */
    protected function trackView(BlogPost $post, Request $request): void
    {
        $user = auth('web')->user();
        $ip = $request->ip();

        // Check if already viewed today by this user/IP
        $existing = BlogView::where('post_id', $post->id)
            ->where(function ($q) use ($user, $ip) {
                if ($user) {
                    $q->where('user_id', $user->id);
                } else {
                    $q->where('ip_address', $ip)->whereNull('user_id');
                }
            })
            ->whereDate('created_at', today())
            ->first();

        if (!$existing) {
            BlogView::create([
                'post_id' => $post->id,
                'user_id' => $user?->id,
                'ip_address' => $ip,
                'user_agent' => substr($request->userAgent() ?? '', 0, 255),
            ]);
            $post->increment('views_count');
        }
    }

    /* ========================================================================
       ADMIN BLOG MANAGEMENT
       ======================================================================== */

    /**
     * Admin blog index — list all posts.
     */
    public function adminIndex()
    {
        $posts = BlogPost::with(['category', 'author'])->latest()->paginate(20);
        $categories = BlogCategory::withCount('posts')->orderBy('name')->get();
        return view('admin.blog', compact('posts', 'categories'));
    }

    /**
     * Admin create/store a blog post.
     */
    public function adminStore(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'category_id' => 'nullable|exists:blog_categories,id',
            'featured_image' => 'nullable|file|image|mimes:jpeg,jpg,png,gif,webp,bmp|max:8192',
            'tags' => 'nullable|string|max:500',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'status' => 'required|in:draft,published,scheduled',
            'is_featured' => 'nullable|boolean',
            'published_at' => 'nullable|date',
        ]);

        $post = new BlogPost();
        $post->title = $validated['title'];
        $post->content = $validated['content'];
        $post->excerpt = $validated['excerpt'] ?? null;
        $post->category_id = $validated['category_id'] ?? null;
        $post->tags = $validated['tags'] ?? null;
        $post->meta_title = $validated['meta_title'] ?? null;
        $post->meta_description = $validated['meta_description'] ?? null;
        $post->status = $validated['status'];
        $post->is_featured = $request->boolean('is_featured');
        $post->author_id = null;
        $post->published_at = $validated['status'] === 'published' ? ($validated['published_at'] ?? now()) : ($validated['published_at'] ?? null);

        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            $path = app(MediaUploadService::class)->storeImage($request->file('featured_image'), 'blog', 1800, 84);
            $post->featured_image = $path;
        }

        $post->save();

        // If published, send real-time notifications to all users
        if ($post->status === 'published') {
            $this->notifyBlogPublish($post);
        }

        return redirect()->route('admin.blog')->with('success', 'Blog post created successfully!');
    }

    /**
     * Admin edit form.
     */
    public function adminEdit(BlogPost $post)
    {
        $post->load(['category', 'author']);
        $categories = BlogCategory::orderBy('name')->get();
        return view('admin.blog-edit', compact('post', 'categories'));
    }

    /**
     * Admin update a blog post.
     */
    public function adminUpdate(Request $request, BlogPost $post)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'category_id' => 'nullable|exists:blog_categories,id',
            'featured_image' => 'nullable|file|image|mimes:jpeg,jpg,png,gif,webp,bmp|max:8192',
            'tags' => 'nullable|string|max:500',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'status' => 'required|in:draft,published,scheduled',
            'is_featured' => 'nullable|boolean',
            'published_at' => 'nullable|date',
        ]);

        $wasDraft = $post->status !== 'published';

        $post->title = $validated['title'];
        $post->content = $validated['content'];
        $post->excerpt = $validated['excerpt'] ?? null;
        $post->category_id = $validated['category_id'] ?? null;
        $post->tags = $validated['tags'] ?? null;
        $post->meta_title = $validated['meta_title'] ?? null;
        $post->meta_description = $validated['meta_description'] ?? null;
        $post->status = $validated['status'];
        $post->is_featured = $request->boolean('is_featured');

        if ($validated['status'] === 'published' && !$post->published_at) {
            $post->published_at = now();
        }

        if ($request->hasFile('featured_image')) {
            if ($post->featured_image) {
                Storage::disk('public')->delete($post->featured_image);
            }
            $path = app(MediaUploadService::class)->storeImage($request->file('featured_image'), 'blog', 1800, 84);
            $post->featured_image = $path;
        }

        $post->save();

        // If newly published, send notifications
        if ($wasDraft && $post->status === 'published') {
            $this->notifyBlogPublish($post);
        }

        return redirect()->route('admin.blog')->with('success', 'Blog post updated successfully!');
    }

    /**
     * Admin delete a blog post.
     */
    public function adminDelete(BlogPost $post)
    {
        if ($post->featured_image) {
            Storage::disk('public')->delete($post->featured_image);
        }
        $post->delete();
        return redirect()->route('admin.blog')->with('success', 'Blog post deleted.');
    }

    /**
     * Admin category management.
     */
    public function adminCategoryStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
        ]);

        BlogCategory::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
        ]);

        return back()->with('success', 'Blog category created.');
    }

    public function adminCategoryDelete(BlogCategory $category)
    {
        $category->delete();
        return back()->with('success', 'Blog category deleted.');
    }

    /**
     * Send real-time notification to all users when a blog is published.
     */
    protected function notifyBlogPublish(BlogPost $post): void
    {
        $users = User::where('banned', false)->get(['id']);

        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'type' => 'blog_published',
                'title' => 'New Blog Post: ' . Str::limit($post->title, 60),
                'body' => $post->excerpt ?? Str::limit(strip_tags($post->content), 120),
                'url' => route('blog.show', $post->slug),
                'is_read' => false,
            ]);
        }
    }
}
