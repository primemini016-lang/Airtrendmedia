<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Polymorphic comments — used by marketplace listings (and reusable by any model).
 * Supports threaded replies via parent_id.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('comments')) {
            Schema::create('comments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->morphs('commentable'); // commentable_type / commentable_id
                $table->foreignId('parent_id')->nullable()->constrained('comments')->cascadeOnDelete();
                $table->text('body');
                $table->boolean('is_approved')->default(true);
                $table->timestamps();
                $table->index(['commentable_type', 'commentable_id', 'is_approved']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
