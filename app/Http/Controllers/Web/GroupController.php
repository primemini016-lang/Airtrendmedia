<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SocialGroup;
use App\Models\SocialGroupMember;
use App\Models\SocialPost;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GroupController extends Controller
{
    /**
     * Browse all groups.
     */
    public function index(Request $request)
    {
        $user = auth('web')->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $query = SocialGroup::with('owner:id,username,name,image');

        // Non-owners can only see public groups and groups they're in
        $query->where(function ($q) use ($user) {
            $q->where('privacy', 'public')
              ->orWhereIn('id', $user->groupMemberships()->pluck('social_group_members.group_id'));
        });

        if ($qstr = $request->input('q')) {
            $query->where(function ($sq) use ($qstr) {
                $sq->where('name', 'like', "%{$qstr}%")
                   ->orWhere('description', 'like', "%{$qstr}%")
                   ->orWhere('category', 'like', "%{$qstr}%");
            });
        }

        if ($cat = $request->input('category')) {
            $query->where('category', $cat);
        }

        $groups = $query->latest()->paginate(12)->appends($request->query());
        $categories = SocialGroup::whereNotNull('category')->distinct()->pluck('category');
        $myGroups = $user->socialGroups()->latest()->limit(5)->get();

        return view('social.groups', compact('groups', 'categories', 'myGroups', 'user'));
    }

    /**
     * Show the create-group form.
     */
    public function create()
    {
        $user = auth('web')->user();
        if (!$user) {
            return redirect()->route('login');
        }
        return view('social.group-create', compact('user'));
    }

    /**
     * Store a new group.
     */
    public function store(Request $request)
    {
        $user = auth('web')->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'name'            => 'required|string|max:120',
            'description'     => 'nullable|string|max:5000',
            'category'        => 'nullable|string|max:80',
            'privacy'         => 'nullable|in:public,private',
            'requires_approval' => 'nullable|boolean',
            'profile_image'   => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'cover_image'     => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        $group = new SocialGroup();
        $group->owner_id = $user->id;
        $group->name = $validated['name'];
        $group->description = $validated['description'] ?? null;
        $group->category = $validated['category'] ?? null;
        $group->privacy = $validated['privacy'] ?? 'public';
        $group->requires_approval = !empty($validated['requires_approval']);
        $group->members_count = 1;
        $group->posts_count = 0;
        $group->is_monetized = false;

        if ($request->hasFile('profile_image')) {
            $group->profile_image = $request->file('profile_image')->store('groups', 'public');
        }
        if ($request->hasFile('cover_image')) {
            $group->cover_image = $request->file('cover_image')->store('groups/covers', 'public');
        }

        $group->save();

        // Owner becomes admin member
        SocialGroupMember::create([
            'group_id' => $group->id,
            'user_id'  => $user->id,
            'role'     => 'admin',
            'status'   => 'approved',
        ]);

        return redirect()->route('social.group.show', $group->slug)
            ->with('success', 'Group created successfully! Invite members to get started.');
    }

    /**
     * Show a single group with its feed.
     */
    public function show(Request $request, SocialGroup $group)
    {
        $user = auth('web')->user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Private groups require membership
        if ($group->privacy === 'private' && !$group->isMemberOf($user) && $group->owner_id !== $user->id) {
            abort(403, 'This is a private group. You must be a member to view it.');
        }

        $membership = $group->members()->where('user_id', $user->id)->first();
        $isMember = $membership && $membership->status === 'approved';
        $isPending = $membership && $membership->status === 'pending';
        $memberRole = $isMember ? $membership->role : null;

        // Only members can see posts (for private groups); public groups show posts to all
        $posts = collect();
        if ($isMember || $group->privacy === 'public') {
            $posts = SocialPost::with(['user:id,username,name,image', 'comments.user', 'likes'])
                ->where('postable_type', SocialGroup::class)
                ->where('postable_id', $group->id)
                ->orderBy('is_pinned', 'desc')
                ->latest()
                ->paginate(10);
        }

        $members = $group->members()
            ->with('user:id,username,name,image')
            ->where('status', 'approved')
            ->latest()
            ->limit(12)
            ->get();

        $pendingMembers = collect();
        if ($group->owner_id === $user->id) {
            $pendingMembers = $group->members()
                ->with('user:id,username,name,image')
                ->where('status', 'pending')
                ->latest()
                ->get();
        }

        $group->load('owner:id,username,name,image');

        return view('social.group-show', compact('group', 'posts', 'isMember', 'isPending', 'memberRole', 'members', 'pendingMembers', 'user'));
    }

    /**
     * Join a group.
     */
    public function join(Request $request, SocialGroup $group)
    {
        $user = auth('web')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $existing = $group->members()->where('user_id', $user->id)->first();
        if ($existing) {
            if ($existing->status === 'approved') {
                return response()->json(['message' => 'Already a member']);
            }
            return response()->json(['message' => 'Join request already pending']);
        }

        $status = $group->requires_approval ? 'pending' : 'approved';

        SocialGroupMember::create([
            'group_id' => $group->id,
            'user_id'  => $user->id,
            'role'     => 'member',
            'status'   => $status,
        ]);

        if ($status === 'approved') {
            $group->increment('members_count');
        }

        // Notify group owner
        if ($group->owner_id !== $user->id) {
            Notification::create([
                'user_id' => $group->owner_id,
                'type'    => 'group_join',
                'title'   => $group->requires_approval ? 'New join request' : 'New group member',
                'body'    => $group->requires_approval
                    ? "{$user->name} requested to join your group \"{$group->name}\"."
                    : "{$user->name} joined your group \"{$group->name}\".",
                'url'     => route('social.group.show', $group->slug),
            ]);
        }

        $message = $group->requires_approval
            ? 'Your join request has been sent. You will be notified when approved.'
            : 'You have joined this group!';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'status'  => $status,
                'members_count' => $group->fresh()->members_count,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Leave a group.
     */
    public function leave(Request $request, SocialGroup $group)
    {
        $user = auth('web')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $membership = $group->members()->where('user_id', $user->id)->first();
        if (!$membership) {
            return response()->json(['message' => 'Not a member']);
        }

        if ($membership->role === 'admin' && $group->owner_id === $user->id) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Group owner cannot leave.'], 403);
            }
            return back()->with('error', 'As the group owner, you cannot leave your own group.');
        }

        if ($membership->status === 'approved') {
            $group->decrement('members_count');
        }
        $membership->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'You left this group.',
                'members_count' => $group->fresh()->members_count,
            ]);
        }

        return back()->with('success', 'You left this group.');
    }

    /**
     * Approve a pending join request (owner/admin only).
     */
    public function approveMember(Request $request, SocialGroup $group, $memberId)
    {
        $user = auth('web')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $myMembership = $group->members()->where('user_id', $user->id)->first();
        if (!$myMembership || !in_array($myMembership->role, ['admin', 'moderator'])) {
            abort(403, 'Only group admins can approve members.');
        }

        $membership = $group->members()->findOrFail($memberId);
        if ($membership->status !== 'pending') {
            return back()->with('info', 'Member is already approved.');
        }

        $membership->update(['status' => 'approved']);
        $group->increment('members_count');

        // Notify the approved user
        Notification::create([
            'user_id' => $membership->user_id,
            'type'    => 'group_approved',
            'title'   => 'Join request approved',
            'body'    => "Your request to join \"{$group->name}\" has been approved. Welcome!",
            'url'     => route('social.group.show', $group->slug),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Member approved.']);
        }

        return back()->with('success', 'Member approved successfully.');
    }

    /**
     * Reject/remove a member (owner/admin only).
     */
    public function removeMember(Request $request, SocialGroup $group, $memberId)
    {
        $user = auth('web')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $myMembership = $group->members()->where('user_id', $user->id)->first();
        if (!$myMembership || !in_array($myMembership->role, ['admin', 'moderator'])) {
            abort(403, 'Only group admins can remove members.');
        }

        $membership = $group->members()->findOrFail($memberId);

        // Can't remove the owner
        if ($membership->user_id === $group->owner_id) {
            return back()->with('error', 'Cannot remove the group owner.');
        }

        if ($membership->status === 'approved') {
            $group->decrement('members_count');
        }
        $membership->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Member removed.']);
        }

        return back()->with('success', 'Member removed.');
    }

    /**
     * Edit group (owner only).
     */
    public function edit(SocialGroup $group)
    {
        $user = auth('web')->user();
        if (!$user || $group->owner_id !== $user->id) {
            abort(403);
        }
        return view('social.group-edit', compact('group', 'user'));
    }

    /**
     * Update group (owner only).
     */
    public function update(Request $request, SocialGroup $group)
    {
        $user = auth('web')->user();
        if (!$user || $group->owner_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name'              => 'required|string|max:120',
            'description'       => 'nullable|string|max:5000',
            'category'          => 'nullable|string|max:80',
            'privacy'           => 'nullable|in:public,private',
            'requires_approval' => 'nullable|boolean',
            'profile_image'     => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'cover_image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        $group->fill($validated);
        $group->requires_approval = !empty($validated['requires_approval']);

        if ($request->hasFile('profile_image')) {
            if ($group->profile_image) {
                Storage::disk('public')->delete($group->profile_image);
            }
            $group->profile_image = $request->file('profile_image')->store('groups', 'public');
        }
        if ($request->hasFile('cover_image')) {
            if ($group->cover_image) {
                Storage::disk('public')->delete($group->cover_image);
            }
            $group->cover_image = $request->file('cover_image')->store('groups/covers', 'public');
        }

        $group->save();

        return redirect()->route('social.group.show', $group->slug)
            ->with('success', 'Group updated successfully.');
    }

    /**
     * Delete group (owner only).
     */
    public function destroy(SocialGroup $group)
    {
        $user = auth('web')->user();
        if (!$user || $group->owner_id !== $user->id) {
            abort(403);
        }

        foreach ($group->posts as $post) {
            if ($post->media) {
                foreach ($post->media as $media) {
                    if (isset($media['path'])) {
                        Storage::disk('public')->delete($media['path']);
                    }
                }
            }
        }

        if ($group->profile_image) {
            Storage::disk('public')->delete($group->profile_image);
        }
        if ($group->cover_image) {
            Storage::disk('public')->delete($group->cover_image);
        }

        $group->delete();

        return redirect()->route('social.groups')
            ->with('success', 'Group deleted successfully.');
    }

    /**
     * Groups owned/joined by the current user.
     */
    public function myGroups()
    {
        $user = auth('web')->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $ownedGroups = $user->socialGroups()->latest()->paginate(12);
        $joinedGroups = $user->groupMemberships()
            ->with('group.owner:id,username,name,image')
            ->where('status', 'approved')
            ->where('role', 'member')
            ->latest()
            ->paginate(12);

        $pendingRequests = $user->groupMemberships()
            ->with('group')
            ->where('status', 'pending')
            ->latest()
            ->get();

        return view('social.my-groups', compact('ownedGroups', 'joinedGroups', 'pendingRequests', 'user'));
    }
}
