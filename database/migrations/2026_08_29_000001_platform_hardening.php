<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'last_seen_at')) $table->timestamp('last_seen_at')->nullable()->index();
            if (!Schema::hasColumn('users', 'online_at')) $table->timestamp('online_at')->nullable()->index();
        });

        Schema::table('affiliate_referrals', function (Blueprint $table) {
            if (!Schema::hasColumn('affiliate_referrals', 'attribution_source')) $table->string('attribution_source', 80)->nullable()->index();
            if (!Schema::hasColumn('affiliate_referrals', 'attribution_ip')) $table->string('attribution_ip', 45)->nullable();
            if (!Schema::hasColumn('affiliate_referrals', 'attributed_at')) $table->timestamp('attributed_at')->nullable()->index();
            if (!Schema::hasColumn('affiliate_referrals', 'trial_started_at')) $table->timestamp('trial_started_at')->nullable();
        });

        Schema::table('messages', function (Blueprint $table) {
            if (!Schema::hasColumn('messages', 'attachments')) $table->json('attachments')->nullable()->after('message');
        });

        // Admin-managed integration settings live in the existing SiteSetting key/value table.
        if (Schema::hasTable('site_settings')) {
            foreach ([
                ['key'=>'affiliate_cookie_days','value'=>'120','group'=>'affiliate'],
                ['key'=>'verification_trial_days','value'=>'3','group'=>'verification'],
                ['key'=>'image_cache_days','value'=>'3','group'=>'system'],
                ['key'=>'ai_image_price','value'=>'0.20','group'=>'ai'],
                ['key'=>'ai_enabled','value'=>'1','group'=>'ai'],
                ['key'=>'ai_max_output_tokens','value'=>'8000','group'=>'ai'],
                ['key'=>'openai_model','value'=>'gpt-4.1-mini','group'=>'ai'],
                ['key'=>'ai_image_model','value'=>'gpt-image-1','group'=>'ai'],
                ['key'=>'flutterwave_base_url','value'=>'https://api.flutterwave.com/v3','group'=>'payment'],
            ] as $setting) {
                if (\Illuminate\Support\Facades\DB::table('site_settings')->where('key',$setting['key'])->doesntExist()) {
                    \Illuminate\Support\Facades\DB::table('site_settings')->insert($setting + ['created_at'=>now(),'updated_at'=>now()]);
                }
            }
        }

        if (!Schema::hasTable('ai_credit_wallets')) {
            Schema::create('ai_credit_wallets', function(Blueprint $table){
                $table->id(); $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
                $table->decimal('credits',12,4)->default(0); $table->timestamps();
            });
        }
        if (!Schema::hasTable('ai_credit_transactions')) {
            Schema::create('ai_credit_transactions', function(Blueprint $table){
                $table->id(); $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->decimal('credits',12,4); $table->enum('type',['purchase','spend','refund'])->default('purchase');
                $table->string('reference',120)->nullable()->index(); $table->text('description')->nullable(); $table->string('provider',40)->nullable();
                $table->timestamps();
            });
        }
    }
    public function down(): void {}
};
