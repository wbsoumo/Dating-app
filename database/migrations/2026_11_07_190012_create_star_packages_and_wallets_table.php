<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('star_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('stars_count');
            $table->decimal('price', 10, 2);
            $table->string('currency', 10)->default('INR');
            $table->integer('discount_percent')->default(0);
            $table->string('badge')->nullable(); // e.g. "Popular", "Best Value"
            $table->boolean('is_active')->default(true)->index();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('star_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->integer('balance')->default(0);
            $table->date('last_daily_bonus_at')->nullable();
            $table->timestamps();
        });

        Schema::create('star_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_id')->constrained('star_wallets')->cascadeOnDelete();
            $table->enum('type', ['credit', 'debit']);
            $table->integer('amount');
            $table->integer('balance_after');
            $table->string('description');
            $table->string('reference_type', 50)->nullable(); // purchase, daily_bonus, swipe_spent, admin_adjustment
            $table->string('reference_id', 100)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('star_transactions');
        Schema::dropIfExists('star_wallets');
        Schema::dropIfExists('star_packages');
    }
};
