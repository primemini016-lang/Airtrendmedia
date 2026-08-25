<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Currencies managed by the admin. Each currency has a USD exchange value
 * so the platform can display prices in the selected currency while Paystack
 * only accepts supported local currencies (NGN, GHS, ZAR, KES).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 6)->unique();        // e.g. USD, NGN, GHS
            $table->string('name', 60);
            $table->string('symbol', 6);
            $table->decimal('usd_value', 14, 6)->default(1.000000); // 1 USD = x units of this currency
            $table->boolean('is_default')->default(false);
            $table->boolean('paystack_supported')->default(false);  // accepted by Paystack directly
            $table->boolean('active')->default(true);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index('active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
