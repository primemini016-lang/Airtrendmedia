<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120)->default('MiniWorkers');
            $table->string('logotext', 60)->default('MiniWorkers');
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->string('url')->nullable();
            $table->foreignId('default_currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->boolean('need_verification')->default(true);
            $table->boolean('saas')->default(true);            // allow users to create tasks/offers
            $table->boolean('manual_payment')->default(true);
            $table->decimal('withdraw_com', 8, 2)->default(10.00);
            $table->decimal('task_com', 8, 2)->default(15.00);
            $table->decimal('activation_fee', 10, 2)->default(5.00);     // in default currency (USD)
            $table->decimal('affiliate_reward', 10, 2)->default(1.50);   // paid to referrer in USD
            $table->boolean('affiliate_enabled')->default(true);
            $table->boolean('ann_status')->default(false);
            $table->text('ann_text')->nullable();
            $table->unsignedInteger('booking_limit')->default(5);
            $table->unsignedInteger('ss_limit')->default(3);
            $table->string('address')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('phone')->nullable();
            $table->text('footer_text')->nullable();
            $table->string('primary_color', 9)->default('#2563eb');
            $table->string('accent_color', 9)->default('#1e40af');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
