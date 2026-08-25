<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskProof;
use App\Services\SettingService;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Employer-facing endpoints: manage tasks you posted, review & approve/reject proofs.
 * Approving a proof pays the worker (wallet credit + transaction log), with the
 * platform commission retained.
 */
class OfferController extends Controller
{
    use ApiResponse;

    public function __construct(private WalletService $wallet) {}

    /**
     * List the current user's posted tasks.
     */
    public function myTasks(Request $request): JsonResponse
    {
        $tasks = Task::with('category')
            ->where('user_id', $request->user('user')->id)
            ->latest()
            ->paginate(20);

        return $this->ok('Your tasks retrieved.', $tasks);
    }

    /**
     * Create a new task (employer). Funds are charged from wallet based on total price.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user('user');

        $validated = $request->validate([
            'title'       => 'required|string|max:191',
            'category_id' => 'required|exists:task_categories,id',
            'price'       => 'required|numeric|min:0.01',
            'amount'      => 'required|integer|min:1',
            'time'        => 'nullable|integer|min:1',
            'action_url'  => 'nullable|url|max:500',
            'details'     => 'required|string|max:5000',
        ]);

        $settings = app(SettingService::class);
        $commission = (float) $settings->get('task_com', 0); // percentage

        $unitPrice = (float) $validated['price'];
        $units = (int) $validated['amount'];
        $totalPrice = round($unitPrice * $units * (1 + $commission / 100), 2);

        if ((float) $user->balance < $totalPrice) {
            return $this->error('Insufficient wallet balance for this task. Please deposit funds first.', 402, [
                'required' => $totalPrice,
                'balance'  => (float) $user->balance,
            ]);
        }

        $task = DB::transaction(function () use ($user, $validated, $totalPrice, $unitPrice) {
            $task = Task::create([
                'code'        => 'T'.strtoupper(\Illuminate\Support\Str::random(10)),
                'title'       => $validated['title'],
                'price'       => $unitPrice,
                'action_url'  => $validated['action_url'] ?? null,
                'details'     => $validated['details'],
                'category_id' => $validated['category_id'],
                'user_id'     => $user->id,
                'date'        => now()->toDateString(),
                'amount'      => $validated['amount'],
                'time'        => $validated['time'] ?? null,
                'total_price' => $totalPrice,
                'status'      => 0, // pending admin review
            ]);

            // Debit the employer's wallet for the total (commission included).
            $this->wallet->debit($user, $totalPrice, 'task_charge', [
                'reference'    => 'TASK-'.$task->code,
                'description'  => 'Funds held for task "'.$task->title.'"',
                'related_id'   => $task->id,
                'related_type' => Task::class,
            ]);

            return $task;
        });

        return $this->ok('Task created. It will appear publicly after admin approval.', $task, 201);
    }

    /**
     * Show proofs submitted for a task owned by the current user.
     */
    public function proofs(Request $request, Task $task): JsonResponse
    {
        if ($task->user_id !== $request->user('user')->id) {
            return $this->error('You do not own this task.', 403);
        }

        $proofs = TaskProof::with('user:id,username,name,image')
            ->where('task_id', $task->id)
            ->latest()
            ->paginate(20);

        return $this->ok('Proofs retrieved.', $proofs);
    }

    /**
     * Approve a proof -> pay the worker their unit price.
     */
    public function approveProof(Request $request, TaskProof $proof): JsonResponse
    {
        $user = $request->user('user');
        $task = $proof->task;

        if ($task->user_id !== $user->id) {
            return $this->error('You do not own this task.', 403);
        }
        if ((int) $proof->status !== 0) {
            return $this->error('This proof has already been processed.', 400);
        }

        DB::transaction(function () use ($proof, $task) {
            $proof->update(['status' => 1]); // approved

            $task->increment('completed');

            // Pay the worker the unit price.
            $this->wallet->credit($proof->user, (float) $task->price, 'task_credit', [
                'reference'    => 'PAY-'.$task->code.'-'.$proof->id,
                'description'  => 'Earnings for task "'.$task->title.'"',
                'related_id'   => $proof->id,
                'related_type' => TaskProof::class,
            ]);

            // If all slots completed, mark task completed.
            if ((int) $task->completed >= (int) $task->amount) {
                $task->update(['status' => 2]);
            }
        });

        return $this->ok('Proof approved. Worker has been paid.');
    }

    /**
     * Reject a proof with an optional note.
     */
    public function rejectProof(Request $request, TaskProof $proof): JsonResponse
    {
        $user = $request->user('user');
        $task = $proof->task;

        if ($task->user_id !== $user->id) {
            return $this->error('You do not own this task.', 403);
        }
        if ((int) $proof->status !== 0) {
            return $this->error('This proof has already been processed.', 400);
        }

        $validated = $request->validate([
            'reject_note' => 'nullable|string|max:1000',
        ]);

        $proof->update([
            'status'      => 2,
            'reject_note' => $validated['reject_note'] ?? 'Proof rejected by employer.',
        ]);

        return $this->ok('Proof rejected.');
    }
}
