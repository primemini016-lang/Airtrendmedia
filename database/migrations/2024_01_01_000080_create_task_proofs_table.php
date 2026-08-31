<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_proofs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->text('comment');
            $table->json('images');
            $table->string('reject_note')->nullable();
            // status: 0 = pending, 1 = approved (paid), 2 = rejected, 3 = disputed
            $table->tinyInteger('status')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'task_id']);
            $table->index(['task_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_proofs');
    }
};
