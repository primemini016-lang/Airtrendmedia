<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Affiliate referrals: tracks each invite and the reward lifecycle.
 * A referral only becomes "rewarded" once the invitee pays the activation fee,
 * which prevents fraud (no reward for unpaid / fake accounts).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('referee_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('reward_amount', 10, 2)->default(0.00);
            $table->string('status', 20)->default('pending');
            // pending: referee registered; paid: referee paid activation & reward credited; revoked: fraud/manual
            $table->foreignId('activation_deposit_id')->nullable()->constrained('deposits')->nullOnDelete();
            $table->foreignId('reward_transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->timestamp('rewarded_at')->nullable();
            $table->string('revoke_reason')->nullable();
            $table->timestamps();

            $table->unique('referee_id'); // one referral record per referee
            $table->index(['referrer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_referrals');
    }
};
