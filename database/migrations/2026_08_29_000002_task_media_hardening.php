<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { if (!Schema::hasColumn('tasks','image')) Schema::table('tasks', fn(Blueprint $t)=>$t->string('image')->nullable()->after('details')); } public function down(): void { if (Schema::hasColumn('tasks','image')) Schema::table('tasks', fn(Blueprint $t)=>$t->dropColumn('image')); } };
