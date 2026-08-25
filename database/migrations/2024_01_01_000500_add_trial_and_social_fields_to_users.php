<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('trial_started_at')->nullable()->after('activated_at');
            $table->timestamp('trial_ends_at')->nullable()->after('trial_started_at');
            $table->string('cover_image')->nullable()->after('image');
            $table->unsignedInteger('followers_count')->default(0)->after('total_earned');
            $table->unsignedInteger('following_count')->default(0)->after('followers_count');
            $table->unsignedInteger('posts_count')->default(0)->after('following_count');
            $table->enum('account_privacy', ['public', 'private'])->default('public')->after('posts_count');
            $table->boolean('monetization_enabled')->default(false)->after('account_privacy');
            $table->index('trial_ends_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['trial_ends_at']);
            $table->dropColumn([
                'trial_started_at', 'trial_ends_at', 'cover_image',
                'followers_count', 'following_count', 'posts_count',
                'account_privacy', 'monetization_enabled',
            ]);
        });
    }
};
