<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user1_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('user2_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['active', 'unmatched'])->default('active')->index();
            $table->timestamp('matched_at')->useCurrent();
            $table->foreignId('unmatched_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('unmatch_reason')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->unique(['user1_id', 'user2_id']);
            $table->index(['user1_id', 'status']);
            $table->index(['user2_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
