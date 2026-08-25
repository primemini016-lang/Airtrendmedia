<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Complaint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Admin complaint/dispute resolution.
 * status: 1 = open, 2 = approved (worker wins -> pay worker), 3 = rejected (employer wins).
 */
class AdminComplaintController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Complaint::with(['proof.task', 'user:id,username,name', 'user2:id,username,name']);
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }
        return $this->ok('Complaints retrieved.', $query->latest()->paginate(25));
    }

    public function show(Complaint $complaint): JsonResponse
    {
        $complaint->load(['proof.task', 'user', 'user2']);
        return $this->ok('Complaint retrieved.', $complaint);
    }

    public function resolve(Request $request, Complaint $complaint): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:2,3',
            'reply'  => 'nullable|string|max:2000',
        ]);

        $complaint->update([
            'status' => $validated['status'],
            'reply'  => $validated['reply'] ?? null,
        ]);

        return $this->ok('Complaint resolved.', $complaint->fresh());
    }
}
