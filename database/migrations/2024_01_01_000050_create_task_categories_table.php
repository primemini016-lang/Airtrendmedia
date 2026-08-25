<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('task_categories')->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('slug')->unique();
            $table->string('icon', 60)->default('briefcase');
            $table->string('color', 20)->default('#2563eb');
            $table->decimal('price', 10, 2)->default(0.00);
            $table->unsignedInteger('min_amount')->default(1);
            $table->boolean('active')->default(true);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_categories');
    }
};
