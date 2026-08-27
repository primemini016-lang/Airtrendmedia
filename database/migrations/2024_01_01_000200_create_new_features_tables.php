<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ---- Notifications ----
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 50);          // job_posted, job_approved, job_rejected, deposit, withdrawal, message, system, etc.
            $table->string('title', 191);
            $table->text('body')->nullable();
            $table->string('url')->nullable();   // link to relevant page
            $table->boolean('is_read')->default(false);
            $table->timestamps();
            $table->index(['user_id', 'is_read']);
        });

        // ---- Ads ----
        Schema::create('ads', function (Blueprint $table) {
            $table->id();
            $table->string('title', 120);
            $table->string('position', 60)->default('header'); // header, footer, sidebar, home_top, home_bottom, browse_top, browse_bottom, custom
            $table->enum('type', ['html', 'image', 'text'])->default('html');
            $table->longText('content')->nullable();      // HTML code or image URL or text
            $table->string('link_url')->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });

        // ---- Gigs (freelancer services) ----
        Schema::create('gigs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('task_categories')->nullOnDelete();
            $table->string('title', 191);
            $table->text('description');
            $table->decimal('price', 10, 2)->default(0);
            $table->string('image')->nullable();
            $table->json('gallery')->nullable();          // multiple images
            $table->string('social_platform')->nullable(); // facebook, instagram, twitter, youtube, etc.
            $table->string('social_url')->nullable();
            $table->enum('status', ['pending', 'active', 'rejected', 'paused'])->default('pending');
            $table->unsignedInteger('views')->default(0);
            $table->unsignedInteger('sales')->default(0);
            $table->timestamps();
            $table->index(['status', 'category_id']);
        });

        // ---- Gig orders (purchases) ----
        Schema::create('gig_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gig_id')->constrained('gigs')->cascadeOnDelete();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->text('requirements')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'delivered', 'completed', 'cancelled', 'disputed'])->default('pending');
            $table->json('proof_images')->nullable();   // buyer/worker proof
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        // ---- Marketplace listings (buy & sell) ----
        Schema::create('marketplace_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('task_categories')->nullOnDelete();
            $table->string('title', 191);
            $table->text('description');
            $table->decimal('price', 10, 2)->default(0);
            $table->enum('listing_type', ['sell', 'buy']); // sell = offering to sell; buy = looking to buy
            $table->string('image')->nullable();
            $table->json('gallery')->nullable();
            $table->string('location')->nullable();
            $table->enum('status', ['pending', 'active', 'sold', 'rejected', 'closed'])->default('pending');
            $table->unsignedInteger('views')->default(0);
            $table->timestamps();
            $table->index(['status', 'listing_type']);
        });

        // ---- Marketplace inquiries / offers ----
        Schema::create('marketplace_inquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained('marketplace_listings')->cascadeOnDelete();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->text('message');
            $table->decimal('offer_price', 10, 2)->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected', 'completed'])->default('pending');
            $table->timestamps();
        });

        // ---- Dynamic site settings (key-value for SMTP, CSS, HTML, etc.) ----
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->longText('value')->nullable();
            $table->string('group', 50)->default('general'); // general, email, payment, appearance, custom_code, system
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_inquiries');
        Schema::dropIfExists('marketplace_listings');
        Schema::dropIfExists('gig_orders');
        Schema::dropIfExists('gigs');
        Schema::dropIfExists('ads');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('site_settings');
    }
};
