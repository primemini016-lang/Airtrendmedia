<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Deposits cover: wallet top-up AND the $5 account activation fee.
 * type = 'wallet' | 'activation'
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('method_id')->nullable()->constrained('deposit_methods')->nullOnDelete();
            $table->decimal('amount', 14, 2);
            $table->decimal('amount_paid', 14, 2)->default(0.00); // what was actually charged (in paystack currency)
            $table->string('type', 20)->default('wallet'); // wallet | activation
            $table->string('reference', 60)->unique()->nullable(); // paystack / manual reference
            $table->boolean('manual')->default(false);
            $table->string('note')->nullable();
            $table->string('reject_note')->nullable();
            $table->tinyInteger('status')->default(0); // 0 = pending, 1 = approved/paid, 2 = rejected
            $table->date('date');
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deposits');
    }
};
