<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Unified review / rating system (polymorphic).
 * Covers: gigs, marketplace_listings, tasks, users (profile 5-star reviews).
 * Each review has a 1-5 star rating + optional written body, a reviewer (user),
 * and a polymorphic "reviewable" target. Unique per (user, reviewable) so one
 * user can only rate a given target once (rating gets updated on re-submit).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('reviews')) {
            Schema::create('reviews', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->morphs('reviewable'); // reviewable_type / reviewable_id
                $table->unsignedTinyInteger('rating');   // 1..5 stars
                $table->text('body')->nullable();        // optional written review
                $table->boolean('is_approved')->default(true);
                $table->timestamps();
                // one review per user per target
                $table->unique(['user_id', 'reviewable_type', 'reviewable_id']);
                $table->index(['reviewable_type', 'reviewable_id', 'is_approved']);
            });
        }

        // Profile-recommendation support on users: cached counts for performance.
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (! Schema::hasColumn('users', 'rating_avg')) {
                    $table->decimal('rating_avg', 3, 2)->default(0.00)->after('total_earned');
                }
                if (! Schema::hasColumn('users', 'rating_count')) {
                    $table->unsignedInteger('rating_count')->default(0)->after('rating_avg');
                }
                if (! Schema::hasColumn('users', 'positive_review_count')) {
                    $table->unsignedInteger('positive_review_count')->default(0)->after('rating_count');
                }
                if (! Schema::hasColumn('users', 'is_recommendable')) {
                    $table->boolean('is_recommendable')->default(false)->after('positive_review_count');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $columns = ['rating_avg', 'rating_count', 'positive_review_count', 'is_recommendable'];
                foreach ($columns as $c) {
                    if (Schema::hasColumn('users', $c)) {
                        $table->dropColumn($c);
                    }
                }
            });
        }
        Schema::dropIfExists('reviews');
    }
};
