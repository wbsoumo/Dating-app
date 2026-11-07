<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('swipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('target_user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['like', 'dislike', 'star'])->index();
            $table->timestamps();

            $table->unique(['user_id', 'target_user_id']);
            $table->index(['user_id', 'created_at']);
            $table->index(['target_user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('swipes');
    }
};
