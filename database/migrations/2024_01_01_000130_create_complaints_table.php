<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proof_id')->constrained('task_proofs')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();   // worker who complained
            $table->foreignId('user2_id')->constrained('users')->cascadeOnDelete();  // employer
            $table->text('details');
            $table->text('reply')->nullable();
            $table->tinyInteger('status')->default(1); // 1 = open, 2 = approved (worker wins), 3 = rejected (employer wins)
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
