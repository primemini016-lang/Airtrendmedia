<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { if (!Schema::hasColumn('verification_badges','trial_started_at')) Schema::table('verification_badges', fn(Blueprint $t)=>$t->timestamp('trial_started_at')->nullable()->after('paid_at')); if (!Schema::hasColumn('verification_badges','trial_ends_at')) Schema::table('verification_badges', fn(Blueprint $t)=>$t->timestamp('trial_ends_at')->nullable()->after('trial_started_at')); } public function down(): void {} };
