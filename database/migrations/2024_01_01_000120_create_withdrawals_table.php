<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('method_id')->constrained('withdrawal_methods')->cascadeOnDelete();
            $table->decimal('amount', 14, 2);
            $table->decimal('fee', 14, 2)->default(0.00);
            $table->decimal('paid', 14, 2)->default(0.00); // amount - fee
            $table->text('details');
            $table->tinyInteger('status')->default(0); // 0 = pending, 1 = paid, 2 = rejected
            $table->string('reject_note')->nullable();
            $table->date('date');
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};
