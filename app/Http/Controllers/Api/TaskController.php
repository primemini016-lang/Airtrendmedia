<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Task;
use App\Models\TaskBooking;
use App\Models\TaskCategory;
use App\Models\TaskProof;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Worker-facing task endpoints: browse, view, book, submit proof, report.
 * Booking & proof submission require an active (paid $5) account.
 */
class TaskController extends Controller
{
    use ApiResponse;

    /**
     * Browse active tasks with optional category/search filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Task::with(['category', 'user:id,username,name,image'])
            ->where('status', 1)
            ->whereColumn('booked', '<', 'amount');

        if ($cat = $request->input('category_id')) {
            $query->where('category_id', $cat);
        }
        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('details', 'like', "%{$search}%");
            });
        }

        $tasks = $query->latest()->paginate(20);

        return $this->ok('Tasks retrieved.', $tasks);
    }

    /**
     * Show a single task (with the current user's booking status).
     */
    public function show(Request $request, Task $task): JsonResponse
    {
        $task->load(['category', 'user:id,username,name,image']);

        $booking = $request->user('user')
            ? $task->bookings()->where('user_id', $request->user('user')->id)->first()
            : null;

        return $this->ok('Task retrieved.', [
            'task'         => $task,
            'already_booked' => (bool) $booking,
            'booking'      => $booking,
        ]);
    }

    /**
     * Book a task (creates a slot reservation with an expiry window).
     */
    public function book(Request $request, Task $task): JsonResponse
    {
        $user = $request->user('user');

        if (! $task->isActive()) {
            return $this->error('This task is not currently available.', 400);
        }

        if ((int) $task->booked >= (int) $task->amount) {
            return $this->error('All slots for this task have been filled.', 400);
        }

        if ($task->user_id === $user->id) {
            return $this->error('You cannot book your own task.', 400);
        }

        $existing = TaskBooking::where('user_id', $user->id)->where('task_id', $task->id)->first();
        if ($existing) {
            return $this->error('You have already booked this task.', 409);
        }

        DB::transaction(function () use ($task, $user) {
            TaskBooking::create([
                'user_id'   => $user->id,
                'task_id'   => $task->id,
                'expire_in' => $task->time ? now()->addMinutes($task->time) : null,
            ]);

            $task->increment('booked');
        });

        return $this->ok('Task booked successfully. Submit your proof before it expires.');
    }

    /**
     * Submit proof of work for a booked task (supports multiple image uploads).
     */
    public function submitProof(Request $request, Task $task): JsonResponse
    {
        $user = $request->user('user');

        $validated = $request->validate([
            'comment'  => 'required|string|max:2000',
            'images'   => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $booking = TaskBooking::where('user_id', $user->id)->where('task_id', $task->id)->first();
        if (! $booking) {
            return $this->error('You must book this task before submitting proof.', 400);
        }
        if ($booking->isExpired()) {
            return $this->error('Your booking has expired. You can no longer submit proof for this task.', 400);
        }

        $images = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $name = 'proof_'.Str::random(16).'.'.$file->getClientOriginalExtension();
                $path = $file->storeAs('proofs', $name, 'public');
                $images[] = Storage::disk('public')->url($path);
            }
        }

        $proof = DB::transaction(function () use ($task, $user, $validated, $images) {
            $proof = TaskProof::create([
                'user_id'  => $user->id,
                'task_id'  => $task->id,
                'comment'  => $validated['comment'],
                'images'   => $images,
                'status'   => 0, // pending
            ]);
            $task->increment('submitted');
            return $proof;
        });

        return $this->ok('Proof submitted. The employer will review it shortly.', $proof);
    }

    /**
     * Report a task / file a complaint about a rejected proof.
     */
    public function report(Request $request, Task $task): JsonResponse
    {
        $user = $request->user('user');

        $validated = $request->validate([
            'proof_id' => 'required|exists:task_proofs,id',
            'details'  => 'required|string|max:3000',
        ]);

        $proof = TaskProof::where('id', $validated['proof_id'])
            ->where('user_id', $user->id)
            ->where('task_id', $task->id)
            ->first();

        if (! $proof) {
            return $this->error('Proof not found for this task.', 404);
        }

        if (Complaint::where('proof_id', $proof->id)->where('user_id', $user->id)->exists()) {
            return $this->error('You have already reported this proof.', 409);
        }

        $complaint = Complaint::create([
            'proof_id'  => $proof->id,
            'user_id'   => $user->id,
            'user2_id'  => $task->user_id,
            'details'   => $validated['details'],
            'status'    => 1,
        ]);

        return $this->ok('Complaint submitted. Admin will review it.', $complaint);
    }

    /**
     * List categories (helper for browse filters).
     */
    public function categories(): JsonResponse
    {
        $categories = TaskCategory::with(['children' => fn ($q) => $q->where('active', true)->orderBy('position')])
            ->whereNull('parent_id')
            ->where('active', true)
            ->orderBy('position')
            ->get();

        return $this->ok('Categories retrieved.', $categories);
    }
}
