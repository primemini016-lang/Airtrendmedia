<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

/**
 * Centralised notification dispatcher.
 *
 * Every meaningful platform event (job posted, job approved/rejected, deposit
 * confirmed, withdrawal processed, new message, system announcement, etc.)
 * flows through here so notifications are consistent and future-proof
 * (e.g. adding websockets / push later only requires changing this class).
 */
class NotificationService
{
    /**
     * Send a notification to a single user.
     */
    public function notify(User $user, string $type, string $title, ?string $body = null, ?string $url = null): Notification
    {
        return Notification::create([
            'user_id' => $user->id,
            'type'    => $type,
            'title'   => $title,
            'body'    => $body,
            'url'     => $url,
        ]);
    }

    /**
     * Send a notification to all active users (broadcast).
     */
    public function broadcast(string $type, string $title, ?string $body = null, ?string $url = null): void
    {
        User::where('banned', false)->chunk(200, function ($users) use ($type, $title, $body, $url) {
            foreach ($users as $user) {
                $this->notify($user, $type, $title, $body, $url);
            }
        });
    }

    // ---- Convenience helpers for common events ----

    public function jobPosted(User $employer, string $taskTitle, string $url): void
    {
        // Notify all active workers that a new task is available
        User::where('banned', false)->where('is_active', true)
            ->where('id', '!=', $employer->id)
            ->chunk(200, function ($workers) use ($taskTitle, $url) {
                foreach ($workers as $worker) {
                    $this->notify($worker, 'job_posted', 'New Task Available', "A new task \"{$taskTitle}\" has been posted.", $url);
                }
            });
    }

    public function jobApproved(User $worker, string $taskTitle, string $url): void
    {
        $this->notify($worker, 'job_approved', 'Task Approved!', "Your proof for \"{$taskTitle}\" has been approved and payment credited.", $url);
    }

    public function jobRejected(User $worker, string $taskTitle, string $reason, string $url): void
    {
        $this->notify($worker, 'job_rejected', 'Task Rejected', "Your proof for \"{$taskTitle}\" was rejected. Reason: {$reason}", $url);
    }

    public function depositConfirmed(User $user, string $amount, string $url): void
    {
        $this->notify($user, 'deposit', 'Deposit Confirmed', "Your deposit of {$amount} has been confirmed and added to your wallet.", $url);
    }

    public function withdrawalProcessed(User $user, string $amount, string $url): void
    {
        $this->notify($user, 'withdrawal', 'Withdrawal Processed', "Your withdrawal request of {$amount} has been processed.", $url);
    }

    public function newMessage(User $user, string $from, string $url): void
    {
        $this->notify($user, 'message', 'New Message', "You have a new message from {$from}.", $url);
    }

    public function accountActivated(User $user): void
    {
        $this->notify($user, 'system', 'Account Activated', 'Your account is now active! You can start earning by completing tasks.', route('user.tasks'));
    }
}
