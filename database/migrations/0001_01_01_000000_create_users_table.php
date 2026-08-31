<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 32)->unique();
            $table->string('name', 120);
            $table->string('email', 191)->unique();
            $table->string('phone', 30)->nullable();
            $table->string('country_code', 4)->nullable();
            $table->string('password', 191);
            $table->string('image')->nullable();
            $table->text('bio')->nullable();
            $table->decimal('balance', 14, 2)->default(0.00);
            $table->decimal('total_earned', 14, 2)->default(0.00);
            $table->unsignedBigInteger('referrer_id')->nullable();
            $table->string('referral_code', 20)->unique();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(false); // paid $5 activation fee
            $table->boolean('banned')->default(false);
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('referrer_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['referrer_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
