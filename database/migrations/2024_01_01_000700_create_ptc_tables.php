<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ptc_ads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('url')->nullable();
            $table->longText('description')->nullable();
            $table->string('image')->nullable();
            $table->unsignedInteger('duration_seconds')->default(10);
            $table->decimal('reward_per_view', 10, 4)->default(0.0050);
            $table->decimal('cost_per_view', 10, 4)->default(0.0050);
            $table->unsignedBigInteger('budget')->default(0);
            $table->unsignedBigInteger('views_count')->default(0);
            $table->unsignedBigInteger('max_views')->default(0);
            $table->enum('status', ['pending', 'approved', 'paused', 'rejected', 'completed'])->default('pending');
            $table->enum('mode', ['automatic', 'manual'])->default('automatic');
            $table->text('admin_note')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('ptc_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ptc_ad_id')->constrained('ptc_ads')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('reward', 10, 4)->default(0);
            $table->unsignedInteger('watched_seconds')->default(0);
            $table->string('ip_address')->nullable();
            $table->enum('status', ['started', 'confirmed', 'cancelled'])->default('started');
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->index(['ptc_ad_id', 'user_id', 'confirmed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ptc_views');
        Schema::dropIfExists('ptc_ads');
    }
};
