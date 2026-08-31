<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Admin task moderation: list, approve/reject posted tasks, force-complete or delete.
 */
class AdminTaskController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Task::with(['category:id,name', 'user:id,username,name']);
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }
        return $this->ok('Tasks retrieved.', $query->latest()->paginate(25));
    }

    public function show(Task $task): JsonResponse
    {
        $task->load(['category', 'user', 'bookings.user:id,username,name', 'proofs.user:id,username,name']);
        return $this->ok('Task retrieved.', $task);
    }

    public function approve(Task $task): JsonResponse
    {
        if ((int) $task->status !== 0) {
            return $this->error('Task is not in a pending state.', 400);
        }
        $task->update(['status' => 1, 'reject_note' => null]);
        return $this->ok('Task approved & now live.', $task->fresh());
    }

    public function reject(Request $request, Task $task): JsonResponse
    {
        $validated = $request->validate(['reject_note' => 'required|string|max:1000']);
        $task->update(['status' => 3, 'reject_note' => $validated['reject_note']]);
        return $this->ok('Task rejected.', $task->fresh());
    }

    public function destroy(Task $task): JsonResponse
    {
        $task->delete();
        return $this->ok('Task deleted.');
    }
}
