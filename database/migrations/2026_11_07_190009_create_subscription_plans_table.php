<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('currency', 10)->default('INR');
            $table->integer('duration_days')->default(30);
            $table->integer('daily_swipe_limit')->default(-1); // -1 = unlimited
            $table->integer('daily_star_allowance')->default(5);
            $table->boolean('can_see_likes')->default(true);
            $table->boolean('can_rewind')->default(true);
            $table->boolean('can_boost')->default(true);
            $table->string('badge_color', 20)->default('#e11d48');
            $table->boolean('is_active')->default(true)->index();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
