<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('order_id')->unique()->index(); // Razorpay order_xxx
            $table->string('payment_id')->nullable()->unique()->index(); // Razorpay pay_xxx
            $table->string('signature')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('INR');
            $table->enum('item_type', ['subscription', 'star_package'])->index();
            $table->unsignedBigInteger('item_id');
            $table->enum('status', ['created', 'authorized', 'captured', 'failed', 'refunded'])->default('created')->index();
            $table->string('method')->nullable(); // upi, card, netbanking, wallet
            $table->json('razorpay_response')->nullable();
            $table->string('error_code')->nullable();
            $table->text('error_description')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['created_at', 'status']);
        });

        Schema::create('payment_refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->string('refund_id')->unique(); // Razorpay rfnd_xxx
            $table->decimal('amount', 10, 2);
            $table->text('reason')->nullable();
            $table->string('status', 50)->default('processed');
            $table->foreignId('processed_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_refunds');
        Schema::dropIfExists('payments');
    }
};
