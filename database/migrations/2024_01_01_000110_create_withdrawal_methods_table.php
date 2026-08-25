<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('withdrawal_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('withdrawal_methods')->cascadeOnDelete();
            $table->string('name', 80);
            $table->string('slug')->unique();
            $table->string('logo')->nullable();
            $table->decimal('min_amount', 10, 2)->default(0.00);
            $table->boolean('active')->default(true);
            $table->boolean('gift_card')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawal_methods');
    }
};
