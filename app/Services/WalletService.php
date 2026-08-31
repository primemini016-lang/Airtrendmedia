<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

/**
 * Central wallet & ledger service. Every balance change goes through here
 * inside a DB transaction and is recorded in the transactions table for
 * full admin oversight and audit.
 */
class WalletService
{
    /**
     * Credit a user's balance and record a transaction.
     */
    public function credit(User $user, float $amount, string $type, array $meta = []): Transaction
    {
        return DB::transaction(function () use ($user, $amount, $type, $meta) {
            // Lock the row to prevent race conditions / double crediting.
            $fresh = User::lockForUpdate()->find($user->id);
            $fresh->balance = (float) $fresh->balance + $amount;
            $fresh->save();

            if (in_array($type, ['task_credit', 'affiliate_reward'], true)) {
                $fresh->total_earned = (float) $fresh->total_earned + $amount;
                $fresh->save();
            }

            return Transaction::create([
                'user_id'        => $fresh->id,
                'type'           => $type,
                'reference'      => $meta['reference'] ?? null,
                'amount'         => abs($amount),
                'balance_after'  => $fresh->balance,
                'currency'       => $meta['currency'] ?? 'USD',
                'status'         => $meta['status'] ?? 'completed',
                'description'    => $meta['description'] ?? null,
                'related_id'     => $meta['related_id'] ?? null,
                'related_type'   => $meta['related_type'] ?? null,
                'ip_address'     => Request::ip(),
            ]);
        });
    }

    /**
     * Debit a user's balance (positive amount passed). Returns false if
     * insufficient funds (does not throw, lets caller handle).
     */
    public function debit(User $user, float $amount, string $type, array $meta = []): Transaction|false
    {
        return DB::transaction(function () use ($user, $amount, $type, $meta) {
            $fresh = User::lockForUpdate()->find($user->id);
            if ((float) $fresh->balance < $amount) {
                return false;
            }

            $fresh->balance = (float) $fresh->balance - $amount;
            $fresh->save();

            return Transaction::create([
                'user_id'        => $fresh->id,
                'type'           => $type,
                'reference'      => $meta['reference'] ?? null,
                'amount'         => -abs($amount),
                'balance_after'  => $fresh->balance,
                'currency'       => $meta['currency'] ?? 'USD',
                'status'         => $meta['status'] ?? 'completed',
                'description'    => $meta['description'] ?? null,
                'related_id'     => $meta['related_id'] ?? null,
                'related_type'   => $meta['related_type'] ?? null,
                'ip_address'     => Request::ip(),
            ]);
        });
    }

    /**
     * Admin manual adjustment of a user's balance (credit or debit).
     */
    public function adminAdjust(User $user, float $amount, string $description): Transaction
    {
        if ($amount >= 0) {
            return $this->credit($user, $amount, 'admin_adjust', [
                'description' => $description,
            ]);
        }
        $txn = $this->debit($user, abs($amount), 'admin_adjust', [
            'description' => $description,
        ]);
        return $txn instanceof Transaction ? $txn : $this->credit($user, 0, 'admin_adjust', [
            'description' => $description.' (insufficient balance, no debit applied)',
            'status'      => 'failed',
        ]);
    }
}
