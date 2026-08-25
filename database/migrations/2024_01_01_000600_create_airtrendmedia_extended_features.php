<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Airtrendmedia extended features:
 *  - KYC submissions (admin auto/manual approval, view docs)
 *  - Verification badge (gov ID + $5 monthly, 30-day validity, auto-expire)
 *  - Stories (Facebook-style disappearing stories)
 *  - Chat typing indicators + message reactions
 *  - Sponsored ads (advertiser purchase $0.02/click, admin review, stats)
 *  - Advertiser/Freelancer account-type switching
 *  - Anti-cheat: per-IP / per-ID account tracking + view-manipulation log
 *  - Push-notification device tokens (Firebase / OneSignal)
 *  - System update log (GitHub auto-update tracking)
 *  - Monetization disable-reason tracking
 */
return new class extends Migration
{
    public function up(): void
    {
        // ===== KYC submissions =====
        if (! Schema::hasTable('kyc_submissions')) {
            Schema::create('kyc_submissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('full_name', 150);
                $table->string('id_type', 60)->default('national_id'); // national_id, passport, drivers_license, voters_card
                $table->string('id_number', 100);
                $table->date('date_of_birth');
                $table->string('country', 80)->nullable();
                $table->string('address')->nullable();
                $table->string('document_front')->nullable();  // storage path
                $table->string('document_back')->nullable();   // storage path
                $table->string('selfie')->nullable();          // storage path
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->text('rejection_reason')->nullable();
                $table->foreignId('reviewed_by')->nullable()->constrained('admins')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();
                $table->index(['user_id', 'status']);
            });
        }

        // ===== Verification badge requests + subscriptions (paid $5 monthly, 30-day) =====
        if (! Schema::hasTable('verification_badges')) {
            Schema::create('verification_badges', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->morphs('verifiable'); // User or SocialPage
                $table->string('full_name', 150)->nullable();
                $table->string('government_id_path')->nullable();
                $table->string('verification_code')->nullable();
                $table->enum('category', ['individual', 'business', 'organization', 'public_figure'])->default('individual');
                $table->decimal('monthly_fee', 8, 2)->default(5.00);
                $table->timestamp('valid_from')->nullable();
                $table->string('id_type', 60)->default('national_id');
                $table->string('id_number', 100)->nullable();
                $table->string('document_path')->nullable();
                $table->string('selfie_path')->nullable();
                $table->enum('status', ['pending', 'verified', 'approved', 'rejected', 'expired', 'revoked'])->default('pending');
                $table->text('rejection_reason')->nullable();
                $table->string('payment_reference')->nullable();
                $table->decimal('amount_paid', 10, 2)->default(5.00);
                $table->timestamp('paid_at')->nullable();
                $table->timestamp('valid_until')->nullable();   // 30 days from approval/payment
                $table->timestamp('expired_at')->nullable();
                $table->foreignId('reviewed_by')->nullable()->constrained('admins')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();
                $table->index(['user_id', 'status']);
                $table->index('valid_until');
            });
        }

        // ===== Stories (Facebook-style) =====
        if (! Schema::hasTable('stories')) {
            Schema::create('stories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->enum('media_type', ['image', 'video', 'text'])->default('image');
                $table->string('media_path')->nullable();
                $table->text('caption')->nullable();
                $table->string('background_color', 20)->nullable();
                $table->unsignedInteger('views_count')->default(0);
                $table->timestamp('expires_at'); // 24h
                $table->boolean('is_pinned')->default(false);
                $table->timestamps();
                $table->index('expires_at');
                $table->index('user_id');
            });
        }

        if (! Schema::hasTable('story_views')) {
            Schema::create('story_views', function (Blueprint $table) {
                $table->id();
                $table->foreignId('story_id')->constrained('stories')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['story_id', 'user_id']);
            });
        }

        // ===== Chat message reactions (unlimited emojis) =====
        if (! Schema::hasTable('chat_reactions')) {
            Schema::create('chat_reactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('chat_message_id')->constrained('chat_messages')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('emoji', 12); // the actual emoji char or shortcode
                $table->timestamps();
                $table->unique(['chat_message_id', 'user_id', 'emoji']);
            });
        }

        // ===== Sponsored / Advertiser ads (in Facebook-clone feed) =====
        if (! Schema::hasTable('sponsored_ads')) {
            Schema::create('sponsored_ads', function (Blueprint $table) {
                $table->id();
                $table->foreignId('advertiser_id')->constrained('users')->cascadeOnDelete();
                $table->string('title', 191);
                $table->text('description')->nullable();
                $table->string('link_url')->nullable();
                $table->enum('ad_type', ['image', 'video', 'text', 'link'])->default('image');
                $table->string('media_path')->nullable();
                $table->string('cta_button', 60)->nullable();      // "Learn More", "Shop Now", etc.
                $table->decimal('cost_per_click', 8, 4)->default(0.0200);
                $table->decimal('budget', 12, 2)->default(0);
                $table->decimal('amount_spent', 12, 2)->default(0);
                $table->unsignedInteger('impressions')->default(0);
                $table->unsignedInteger('clicks')->default(0);
                $table->unsignedInteger('views')->default(0);
                $table->enum('status', ['draft', 'pending_review', 'approved', 'rejected', 'paused', 'completed', 'expired', 'budget_exhausted'])->default('pending_review');
                $table->text('rejection_reason')->nullable();
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->foreignId('reviewed_by')->nullable()->constrained('admins')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();
                $table->index(['advertiser_id', 'status']);
                $table->index('status');
            });
        }

        // Track ad clicks (anti-cheat + analytics)
        if (! Schema::hasTable('sponsored_ad_clicks')) {
            Schema::create('sponsored_ad_clicks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sponsored_ad_id')->constrained('sponsored_ads')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent')->nullable();
                $table->decimal('cost', 8, 4)->default(0.0200);
                $table->timestamps();
                $table->index(['sponsored_ad_id', 'ip_address']);
            });
        }

        // Track ad impressions
        if (! Schema::hasTable('sponsored_ad_impressions')) {
            Schema::create('sponsored_ad_impressions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sponsored_ad_id')->constrained('sponsored_ads')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('ip_address', 45)->nullable();
                $table->timestamps();
                $table->index(['sponsored_ad_id', 'ip_address']);
            });
        }

        // ===== Push-notification device tokens (Firebase / OneSignal) =====
        if (! Schema::hasTable('push_device_tokens')) {
            Schema::create('push_device_tokens', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('token', 255);
                $table->string('device_id', 100)->nullable();
                $table->enum('platform', ['web', 'android', 'ios', 'pwa'])->default('web');
                $table->string('provider', 30)->default('firebase'); // firebase, onesignal
                $table->boolean('is_active')->default(true);
                $table->timestamp('last_used_at')->nullable();
                $table->timestamps();
                $table->index(['user_id', 'is_active']);
                $table->index('token');
            });
        }

        // ===== Push notification log (for slides/images native notifications) =====
        if (! Schema::hasTable('push_notifications_log')) {
            Schema::create('push_notifications_log', function (Blueprint $table) {
                $table->id();
                $table->string('type', 60)->default('broadcast'); // broadcast, individual
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('title', 191);
                $table->text('body')->nullable();
                $table->string('image_url')->nullable();
                $table->json('slides')->nullable();      // for carousel/slideshow notifications
                $table->string('url')->nullable();
                $table->string('icon')->nullable();
                $table->string('badge')->nullable();
                $table->text('data')->nullable();         // custom data payload
                $table->string('provider', 30)->default('firebase');
                $table->unsignedInteger('recipients')->default(0);
                $table->unsignedInteger('sent_count')->default(0);
                $table->unsignedInteger('failed_count')->default(0);
                $table->string('status', 20)->default('sent'); // sent, failed, pending
                $table->text('error')->nullable();
                $table->text('provider_response')->nullable();
                $table->timestamps();
            });
        }

        // ===== Anti-cheat: account IP tracking =====
        if (! Schema::hasTable('user_ip_logs')) {
            Schema::create('user_ip_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('ip_address', 45);
                $table->string('user_agent')->nullable();
                $table->string('event', 50)->default('login'); // login, register, view, click
                $table->timestamps();
                $table->index(['ip_address', 'event']);
                $table->index('user_id');
            });
        }

        // Anti-cheat: suspicious activity flags
        if (! Schema::hasTable('anti_cheat_flags')) {
            Schema::create('anti_cheat_flags', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('type', 60); // multiple_accounts, view_manipulation, fake_engagement, duplicate_ip, duplicate_id
                $table->text('description')->nullable();
                $table->json('evidence')->nullable();
                $table->string('severity', 20)->default('medium'); // low, medium, high
                $table->string('ip_address', 45)->nullable();
                $table->enum('status', ['open', 'reviewing', 'resolved', 'dismissed'])->default('open');
                $table->foreignId('resolved_by')->nullable()->constrained('admins')->nullOnDelete();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();
                $table->index(['user_id', 'status']);
                $table->index('type');
            });
        }

        // ===== System update log (GitHub auto-update) =====
        if (! Schema::hasTable('system_update_logs')) {
            Schema::create('system_update_logs', function (Blueprint $table) {
                $table->id();
                $table->string('from_commit', 60)->nullable();
                $table->string('to_commit', 60)->nullable();
                $table->string('from_version', 30)->nullable();
                $table->string('to_version', 30)->nullable();
                $table->text('output')->nullable();
                $table->enum('status', ['started', 'success', 'failed'])->default('started');
                $table->foreignId('initiated_by')->nullable()->constrained('admins')->nullOnDelete();
                $table->timestamps();
            });
        }

        // ===== Monetization disable reasons (admin can disable with reason) =====
        if (! Schema::hasTable('monetization_disable_logs')) {
            Schema::create('monetization_disable_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->text('reason');
                $table->enum('action', ['disabled', 'enabled'])->default('disabled');
                $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
                $table->timestamp('disabled_at')->useCurrent();
                $table->timestamps();
                $table->index('user_id');
            });
        }

        // ===== Extensions to existing tables =====

        // Users: account_type (freelancer/advertiser switch), KYC + verification status, registration IP
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'account_type')) {
                $table->enum('account_type', ['freelancer', 'advertiser', 'both'])->default('freelancer')->after('banned');
            }
            if (! Schema::hasColumn('users', 'kyc_status')) {
                $table->enum('kyc_status', ['none', 'pending', 'approved', 'rejected'])->default('none')->after('account_type');
            }
            if (! Schema::hasColumn('users', 'kyc_approved_at')) {
                $table->timestamp('kyc_approved_at')->nullable()->after('kyc_status');
            }
            if (! Schema::hasColumn('users', 'verification_status')) {
                $table->enum('verification_status', ['none', 'pending', 'verified', 'expired', 'rejected'])->default('none')->after('kyc_approved_at');
            }
            if (! Schema::hasColumn('users', 'verification_valid_until')) {
                $table->timestamp('verification_valid_until')->nullable()->after('verification_status');
            }
            if (! Schema::hasColumn('users', 'registration_ip')) {
                $table->string('registration_ip', 45)->nullable()->after('verification_valid_until');
            }
            if (! Schema::hasColumn('users', 'monetization_disabled_reason')) {
                $table->text('monetization_disabled_reason')->nullable()->after('monetization_enabled');
            }
            if (! Schema::hasColumn('users', 'monetization_disabled_at')) {
                $table->timestamp('monetization_disabled_at')->nullable()->after('monetization_disabled_reason');
            }
        });

        // Monetization eligibility: add Airtrendmedia-specific requirement tracking columns
        Schema::table('monetization_eligibility', function (Blueprint $table) {
            if (! Schema::hasColumn('monetization_eligibility', 'paid_followers')) {
                $table->unsignedInteger('paid_followers')->default(0)->after('followers_count');
            }
            if (! Schema::hasColumn('monetization_eligibility', 'eligible_views')) {
                $table->unsignedInteger('eligible_views')->default(0)->after('paid_followers');
            }
            if (! Schema::hasColumn('monetization_eligibility', 'real_engagement')) {
                $table->unsignedInteger('real_engagement')->default(0)->after('eligible_views');
            }
            if (! Schema::hasColumn('monetization_eligibility', 'auto_enabled_at')) {
                $table->timestamp('auto_enabled_at')->nullable()->after('approved_at');
            }
        });

        // Chat messages: typing support is handled via cache, but add read receipts
        Schema::table('chat_messages', function (Blueprint $table) {
            if (! Schema::hasColumn('chat_messages', 'reactions_count')) {
                $table->unsignedInteger('reactions_count')->default(0)->after('body');
            }
        });

        // Task categories: icon field (for exact social media interaction icons)
        Schema::table('task_categories', function (Blueprint $table) {
            if (! Schema::hasColumn('task_categories', 'icon')) {
                $table->string('icon', 60)->default('briefcase')->after('name');
            }
            if (! Schema::hasColumn('task_categories', 'platform')) {
                $table->string('platform', 60)->nullable()->after('icon'); // facebook, instagram, youtube, etc.
            }
        });

        // App settings: add all the new admin-managed toggles/config
        Schema::table('app_settings', function (Blueprint $table) {
            $cols = [
                'kyc_required_for_withdrawal' => 'boolean|true',
                'kyc_auto_approval' => 'boolean|false',
                'verification_badge_fee' => 'decimal:10,2|5.00',
                'verification_badge_duration_days' => 'integer|30',
                'verification_auto_approval' => 'boolean|false',
                'ad_auto_approval' => 'boolean|false',
                'ad_cost_per_click' => 'decimal:8,4|0.0200',
                'microjob_auto_approval' => 'boolean|false',
                'withdrawal_auto_approval' => 'boolean|false',
                'blog_auto_approval' => 'boolean|false',
                'email_verification_mode' => 'string:30|auto', // auto or manual
                'monetization_min_followers' => 'integer|500',
                'monetization_min_views' => 'integer|1000',
                'monetization_min_engagement' => 'integer|1000',
                'monetization_auto_enable' => 'boolean|true',
                'firebase_server_key' => 'string|nullable',
                'firebase_project_id' => 'string|nullable',
                'firebase_web_api_key' => 'string|nullable',
                'firebase_sender_id' => 'string|nullable',
                'firebase_auth_domain' => 'string|nullable',
                'firebase_storage_bucket' => 'string|nullable',
                'firebase_app_id' => 'string|nullable',
                'firebase_measurement_id' => 'string|nullable',
                'firebase_messaging_vapid_key' => 'string|nullable',
                'firebase_config_json' => 'text|nullable',
                'onesignal_app_id' => 'string|nullable',
                'onesignal_rest_api_key' => 'string|nullable',
                'onesignal_safari_web_id' => 'string|nullable',
                'pwa_enabled' => 'boolean|true',
                'pwa_name' => 'string|nullable',
                'pwa_short_name' => 'string|nullable',
                'pwa_theme_color' => 'string|nullable',
                'pwa_background_color' => 'string|nullable',
                'pwa_display' => 'string|standalone',
                'pwa_orientation' => 'string|any',
                'pwa_icon_192' => 'string|nullable',
                'pwa_icon_512' => 'string|nullable',
                'pwa_apple_touch_icon' => 'string|nullable',
                'pwa_custom_css' => 'text|nullable',
                'pwa_offline_enabled' => 'boolean|true',
                'pwa_start_url' => 'string|/',
                'sound_enabled' => 'boolean|true',
                'sound_like' => 'string|nullable',
                'sound_comment' => 'string|nullable',
                'sound_message' => 'string|nullable',
                'sound_notification' => 'string|nullable',
                'sound_send' => 'string|nullable',
                'anti_cheat_enabled' => 'boolean|true',
                'one_account_per_ip' => 'boolean|true',
                'one_account_per_id' => 'boolean|true',
                'min_interaction_price' => 'decimal:8,4|0.0100',
                'github_repo' => 'string|nullable',
                'github_branch' => 'string|main',
                'auto_update_enabled' => 'boolean|true',
                'current_version' => 'string|1.0.0',
                'ai_features_enabled' => 'boolean|true',
                'content_moderation_ai' => 'boolean|true',
                'smart_feed_algorithm' => 'boolean|true',
                'offling_page_enabled' => 'boolean|true',
            ];
            foreach ($cols as $col => $def) {
                if (! Schema::hasColumn('app_settings', $col)) {
                    [$type, $default] = explode('|', $def, 2);
                    if ($type === 'boolean') {
                        $table->boolean($col)->default($default === 'true')->after('accent_color');
                    } elseif (str_starts_with($type, 'decimal')) {
                        $prec = explode(',', explode(':', $type)[1] ?? '8,2');
                        $table->decimal($col, (int)($prec[0] ?? 8), (int)($prec[1] ?? 2))->default((float)$default)->after('accent_color');
                    } elseif (str_starts_with($type, 'integer')) {
                        $table->integer($col)->default((int)$default)->after('accent_color');
                    } elseif (str_starts_with($type, 'string')) {
                        $len = (int)(explode(':', $type)[1] ?? 191);
                        $colDef = $table->string($col, $len)->after('accent_color');
                        if ($default === 'nullable') {
                            $colDef->nullable();
                        } else {
                            $colDef->default($default);
                        }
                    } elseif ($type === 'text') {
                        $table->text($col)->nullable()->after('accent_color');
                    }
                }
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monetization_disable_logs');
        Schema::dropIfExists('system_update_logs');
        Schema::dropIfExists('anti_cheat_flags');
        Schema::dropIfExists('user_ip_logs');
        Schema::dropIfExists('push_notifications_log');
        Schema::dropIfExists('push_device_tokens');
        Schema::dropIfExists('sponsored_ad_impressions');
        Schema::dropIfExists('sponsored_ad_clicks');
        Schema::dropIfExists('sponsored_ads');
        Schema::dropIfExists('chat_reactions');
        Schema::dropIfExists('story_views');
        Schema::dropIfExists('stories');
        Schema::dropIfExists('verification_badges');
        Schema::dropIfExists('kyc_submissions');
    }
};
