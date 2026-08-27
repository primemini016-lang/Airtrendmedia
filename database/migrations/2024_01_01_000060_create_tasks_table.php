<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('title', 191);
            $table->decimal('price', 10, 2);
            $table->string('action_url')->nullable();
            $table->text('details');
            $table->foreignId('category_id')->constrained('task_categories')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('date');
            $table->unsignedInteger('amount');
            $table->unsignedInteger('time')->nullable(); // minutes to complete
            $table->decimal('total_price', 14, 2)->default(0.00);
            $table->unsignedInteger('booked')->default(0);
            $table->unsignedInteger('submitted')->default(0);
            $table->unsignedInteger('completed')->default(0);
            // status: 0 = pending review (admin), 1 = active, 2 = completed, 3 = rejected, 4 = full
            $table->tinyInteger('status')->default(0);
            $table->string('reject_note')->nullable();
            $table->timestamps();

            $table->index(['status', 'date']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
