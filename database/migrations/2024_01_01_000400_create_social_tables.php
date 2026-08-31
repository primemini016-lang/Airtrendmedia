<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ---- Follows (follower / following) ----
        Schema::create('follows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('follower_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('following_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_accepted')->default(true); // true for public accounts
            $table->timestamps();
            $table->unique(['follower_id', 'following_id']);
            $table->index('following_id');
        });

        // ---- Social Posts (Facebook-style feed posts) ----
        Schema::create('social_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->morphs('postable'); // user, page, or group
            $table->longText('content')->nullable();
            $table->json('media')->nullable();       // images, video URLs
            $table->string('link_url')->nullable();
            $table->string('link_title')->nullable();
            $table->string('link_description')->nullable();
            $table->string('link_image')->nullable();
            $table->enum('visibility', ['public', 'friends', 'private'])->default('public');
            $table->enum('feeling', ['happy', 'sad', 'excited', 'loved', 'grateful', 'blessed', 'tired', 'motivated', 'celebrating'])->nullable();
            $table->string('location')->nullable();
            $table->string('background_color', 20)->nullable();
            $table->unsignedInteger('likes_count')->default(0);
            $table->unsignedInteger('comments_count')->default(0);
            $table->unsignedInteger('shares_count')->default(0);
            $table->unsignedInteger('views_count')->default(0);
            $table->boolean('is_monetized')->default(false);
            $table->boolean('is_pinned')->default(false);
            $table->timestamps();
            $table->index(['postable_type', 'postable_id', 'created_at']);
            $table->index(['user_id', 'visibility']);
        });

        // ---- Social Comments ----
        Schema::create('social_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('social_posts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('social_comments')->cascadeOnDelete();
            $table->text('body');
            $table->json('media')->nullable();
            $table->unsignedInteger('likes_count')->default(0);
            $table->unsignedInteger('replies_count')->default(0);
            $table->timestamps();
            $table->index(['post_id', 'parent_id']);
        });

        // ---- Social Likes (polymorphic - posts + comments) ----
        Schema::create('social_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->morphs('likeable'); // social_posts or social_comments
            $table->string('reaction', 20)->default('like'); // like, love, haha, wow, sad, angry
            $table->timestamps();
            $table->unique(['user_id', 'likeable_type', 'likeable_id']);
        });

        // ---- Social Shares ----
        Schema::create('social_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('social_posts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->index('post_id');
        });

        // ---- Social Pages (Facebook Pages) ----
        Schema::create('social_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('slug', 180)->unique();
            $table->text('description')->nullable();
            $table->string('category', 100)->nullable();
            $table->string('profile_image')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('website')->nullable();
            $table->string('location')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->enum('verification_status', ['unverified', 'pending', 'verified'])->default('unverified');
            $table->unsignedInteger('followers_count')->default(0);
            $table->unsignedInteger('likes_count')->default(0);
            $table->boolean('is_monetized')->default(false);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
            $table->index('slug');
        });

        // ---- Page Members / Followers ----
        Schema::create('social_page_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('social_pages')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('role', ['owner', 'admin', 'editor', 'member'])->default('member');
            $table->timestamps();
            $table->unique(['page_id', 'user_id']);
        });

        // ---- Social Groups (Facebook Groups) ----
        Schema::create('social_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('slug', 180)->unique();
            $table->text('description')->nullable();
            $table->string('category', 100)->nullable();
            $table->string('profile_image')->nullable();
            $table->string('cover_image')->nullable();
            $table->enum('privacy', ['public', 'private', 'secret'])->default('public');
            $table->boolean('requires_approval')->default(false);
            $table->unsignedInteger('members_count')->default(1);
            $table->unsignedInteger('posts_count')->default(0);
            $table->boolean('is_monetized')->default(false);
            $table->timestamps();
            $table->index('slug');
        });

        // ---- Group Members ----
        Schema::create('social_group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('social_groups')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('role', ['owner', 'admin', 'moderator', 'member'])->default('member');
            $table->enum('status', ['pending', 'approved', 'banned'])->default('approved');
            $table->timestamps();
            $table->unique(['group_id', 'user_id']);
        });

        // ---- Conversations (1-on-1 and group chats) ----
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->nullable(); // for group chats
            $table->boolean('is_group')->default(false);
            $table->string('group_avatar')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
        });

        // ---- Conversation Participants ----
        Schema::create('conversation_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('role', ['member', 'admin'])->default('member');
            $table->timestamp('last_read_at')->nullable();
            $table->boolean('is_muted')->default(false);
            $table->timestamps();
            $table->unique(['conversation_id', 'user_id']);
        });

        // ---- Chat Messages ----
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->longText('body')->nullable();
            $table->json('attachments')->nullable();
            $table->string('message_type', 30)->default('text'); // text, image, file, sticker, gif
            $table->foreignId('reply_to_id')->nullable()->constrained('chat_messages')->cascadeOnDelete();
            $table->unsignedInteger('reactions_count')->default(0);
            $table->timestamps();
            $table->index(['conversation_id', 'created_at']);
        });

        // ---- Monetization Eligibility (Facebook-style Partner Monetization) ----
        Schema::create('monetization_eligibility', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_eligible')->default(false);
            $table->boolean('content_monetization')->default(false);   // in-stream ads on content
            $table->boolean('fan_subscriptions')->default(false);       // monthly subscriptions
            $table->boolean('stars_enabled')->default(false);           // viewers send stars (tips)
            $table->unsignedInteger('followers_count')->default(0);    // snapshot at eligibility
            $table->unsignedInteger('content_count')->default(0);
            $table->unsignedInteger('engagement_score')->default(0);
            $table->string('country', 60)->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->index('user_id');
        });

        // ---- Monetization Earnings ----
        Schema::create('monetization_earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('earning_category', 40);   // content_ad, subscription, stars, page_monetization
            $table->morphs('source');                  // social_posts, social_pages, etc. (source_type + source_id)
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('currency', 10)->default('USD');
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('stars_count')->default(0);
            $table->text('metadata')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'earning_category']);
        });

        // ---- Content Subscriptions (fans subscribe to creators) ----
        Schema::create('content_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscriber_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('monthly_amount', 10, 2)->default(0);
            $table->enum('status', ['active', 'cancelled', 'expired', 'paused'])->default('active');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
            $table->unique(['subscriber_id', 'creator_id']);
        });

        // ---- Stars (tips sent to creators) ----
        Schema::create('content_stars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('receiver_id')->constrained('users')->cascadeOnDelete();
            $table->morphs('starable'); // social_posts, social_pages
            $table->unsignedInteger('stars_count')->default(1);
            $table->text('message')->nullable();
            $table->decimal('monetary_value', 10, 4)->default(0);
            $table->timestamps();
            $table->index(['receiver_id', 'starable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_stars');
        Schema::dropIfExists('content_subscriptions');
        Schema::dropIfExists('monetization_earnings');
        Schema::dropIfExists('monetization_eligibility');
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('conversation_participants');
        Schema::dropIfExists('conversations');
        Schema::dropIfExists('social_group_members');
        Schema::dropIfExists('social_groups');
        Schema::dropIfExists('social_page_members');
        Schema::dropIfExists('social_pages');
        Schema::dropIfExists('social_shares');
        Schema::dropIfExists('social_likes');
        Schema::dropIfExists('social_comments');
        Schema::dropIfExists('social_posts');
        Schema::dropIfExists('follows');
    }
};
