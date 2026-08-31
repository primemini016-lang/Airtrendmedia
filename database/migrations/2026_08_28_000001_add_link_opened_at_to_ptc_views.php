<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('ptc_views', 'link_opened_at')) {
            Schema::table('ptc_views', function (Blueprint $table) {
                $table->timestamp('link_opened_at')->nullable()->after('started_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('ptc_views', 'link_opened_at')) {
            Schema::table('ptc_views', function (Blueprint $table) {
                $table->dropColumn('link_opened_at');
            });
        }
    }
};
