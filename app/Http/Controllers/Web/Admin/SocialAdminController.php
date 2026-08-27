<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialComment;
use App\Models\SocialGroup;
use App\Models\SocialPage;
use App\Models\SocialPost;
use App\Models\Story;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SocialAdminController extends Controller
{
    private function guard(): void
    {
        abort_unless(auth('admin')->user()?->isSuper(), 403, 'Super administrator access required.');
    }

    public function dashboard()
    {
        $this->guard();
        $stats = [
            'posts' => SocialPost::count(),
            'comments' => SocialComment::count(),
            'stories' => Story::count(),
            'pages' => SocialPage::count(),
            'groups' => SocialGroup::count(),
            'users' => User::count(),
            'videos' => SocialPost::where('media', 'like', '%"type":"video"%')->count(),
            'blogs' => DB::table('blog_posts')->count(),
        ];
        $recentPosts = SocialPost::with(['user','postable'])->latest()->limit(12)->get();
        return view('admin.control-center', compact('stats','recentPosts'));
    }

    public function posts(Request $request)
    {
        $this->guard();
        $query = SocialPost::with(['user','postable'])->latest();
        if ($request->filled('q')) $query->where('content','like','%'.$request->q.'%');
        $posts = $query->paginate(25)->withQueryString();
        return view('admin.social.posts', compact('posts'));
    }

    public function updatePost(Request $request, SocialPost $post)
    {
        $this->guard();
        $data = $request->validate([
            'content' => 'nullable|string|max:5000',
            'visibility' => 'required|in:public,friends,private',
            'is_pinned' => 'nullable|boolean',
            'is_monetized' => 'nullable|boolean',
        ]);
        $post->update($data + ['is_pinned' => $request->boolean('is_pinned'), 'is_monetized' => $request->boolean('is_monetized')]);
        return back()->with('success','Social post updated by administrator.');
    }

    public function deletePost(SocialPost $post)
    {
        $this->guard();
        $post->delete();
        return back()->with('success','Social post removed from the platform.');
    }


    public function comments(Request $request)
    {
        $this->guard();
        $query = SocialComment::with(['user','post'])->latest();
        if ($request->filled('q')) {
            $q = trim((string) $request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('body', 'like', '%'.$q.'%')
                    ->orWhereHas('user', fn ($u) => $u->where('name','like','%'.$q.'%')->orWhere('username','like','%'.$q.'%'));
            });
        }
        $comments = $query->paginate(30)->withQueryString();
        return view('admin.social.comments', compact('comments'));
    }

    public function deleteComment(SocialComment $comment)
    {
        $this->guard();
        DB::transaction(function () use ($comment) {
            $post = $comment->post;
            $parent = $comment->parent;
            $replyCount = $comment->replies()->count();
            $comment->replies()->delete();
            $comment->delete();
            if ($post) {
                $post->update(['comments_count' => max(0, (int) $post->comments_count - 1 - $replyCount)]);
            }
            if ($parent) {
                $parent->update(['replies_count' => max(0, (int) $parent->replies_count - $replyCount)]);
            }
        });
        return back()->with('success','Comment and its replies removed by administrator.');
    }

    public function stories(Request $request)
    {
        $this->guard();
        $query = Story::with('user')->latest();
        if ($request->filled('q')) {
            $q = trim((string) $request->q);
            $query->where('caption','like','%'.$q.'%');
        }
        $stories = $query->paginate(30)->withQueryString();
        return view('admin.social.stories', compact('stories'));
    }

    public function deleteStory(Story $story)
    {
        $this->guard();
        $story->views()->delete();
        $story->delete();
        return back()->with('success','Story removed by administrator.');
    }

    public function pages(Request $request)
    {
        $this->guard();
        $query = SocialPage::with('owner')->latest();
        if ($request->filled('q')) $query->where('name','like','%'.$request->q.'%');
        $pages = $query->paginate(25)->withQueryString();
        return view('admin.social.pages', compact('pages'));
    }

    public function updatePage(Request $request, SocialPage $page)
    {
        $this->guard();
        $data = $request->validate(['name'=>'required|string|max:150','description'=>'nullable|string|max:5000','category'=>'nullable|string|max:100','verification_status'=>'required|in:unverified,pending,verified','is_published'=>'nullable|boolean','is_monetized'=>'nullable|boolean']);
        $page->update($data + ['is_published'=>$request->boolean('is_published'),'is_monetized'=>$request->boolean('is_monetized')]);
        return back()->with('success','Page updated.');
    }

    public function deletePage(SocialPage $page)
    {
        $this->guard();
        SocialPost::where('postable_type', SocialPage::class)->where('postable_id', $page->id)->delete();
        $page->delete();
        return back()->with('success','Page and its social posts deleted.');
    }

    public function groups(Request $request)
    {
        $this->guard();
        $query = SocialGroup::with('owner')->latest();
        if ($request->filled('q')) $query->where('name','like','%'.$request->q.'%');
        $groups = $query->paginate(25)->withQueryString();
        return view('admin.social.groups', compact('groups'));
    }

    public function updateGroup(Request $request, SocialGroup $group)
    {
        $this->guard();
        $data = $request->validate(['name'=>'required|string|max:150','description'=>'nullable|string|max:5000','category'=>'nullable|string|max:100','privacy'=>'required|in:public,private,secret','is_monetized'=>'nullable|boolean']);
        $group->update($data + ['is_monetized'=>$request->boolean('is_monetized')]);
        return back()->with('success','Group updated.');
    }

    public function deleteGroup(SocialGroup $group)
    {
        $this->guard();
        SocialPost::where('postable_type', SocialGroup::class)->where('postable_id', $group->id)->delete();
        $group->delete();
        return back()->with('success','Group and its social posts deleted.');
    }
}
