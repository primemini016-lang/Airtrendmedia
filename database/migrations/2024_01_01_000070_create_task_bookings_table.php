<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->dateTime('expire_in')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'task_id']);
            $table->index('expire_in');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_bookings');
    }
};
