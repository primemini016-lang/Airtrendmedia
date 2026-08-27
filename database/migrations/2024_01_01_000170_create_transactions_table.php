<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Centralised, immutable transaction log for admin oversight.
 * Every credit/debit movement on a user balance is recorded here.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 30);          // deposit, activation, withdrawal, task_credit, task_charge, task_refund, affiliate_reward, admin_adjust, withdraw_fee
            $table->string('reference', 80)->nullable();
            $table->decimal('amount', 14, 2);    // positive = credit, negative = debit
            $table->decimal('balance_after', 14, 2)->default(0.00);
            $table->string('currency', 6)->default('USD');
            $table->string('status', 20)->default('completed'); // completed, pending, failed
            $table->text('description')->nullable();
            $table->foreignId('related_id')->nullable(); // generic relation (task, deposit, withdrawal...)
            $table->string('related_type', 60)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type']);
            $table->index('reference');
            $table->index(['related_type', 'related_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
