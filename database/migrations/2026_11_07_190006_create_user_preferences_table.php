<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('gender_preference', ['all', 'male', 'female', 'non_binary'])->default('all');
            $table->integer('min_age')->default(18);
            $table->integer('max_age')->default(60);
            $table->integer('max_distance_km')->default(50);
            $table->string('preferred_religion')->nullable();
            $table->string('preferred_relationship_goal')->nullable();
            $table->boolean('show_verified_only')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};
