<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            // Admin-created articles do not belong to a normal user account.
            // Keep the existing author relation, but allow system/admin posts.
            $table->foreignId('author_id')->nullable()->change();
            $table->string('tags', 500)->nullable()->after('meta_keywords');
            $table->string('meta_title', 255)->nullable()->after('meta_keywords');
            $table->unsignedInteger('ratings_count')->default(0)->after('shares_count');
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn(['tags', 'meta_title', 'ratings_count']);
            $table->foreignId('author_id')->nullable(false)->change();
        });
    }
};
