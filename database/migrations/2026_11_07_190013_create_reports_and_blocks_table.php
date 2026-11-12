<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user_reports')) {
            Schema::create('user_reports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('reported_user_id')->constrained('users')->cascadeOnDelete();
                $table->string('reason'); // fake_profile, harassment, inappropriate_photos, spam, other
                $table->text('details')->nullable();
                $table->enum('status', ['pending', 'reviewed', 'resolved', 'dismissed'])->default('pending')->index();
                $table->text('admin_notes')->nullable();
                $table->foreignId('resolved_by')->nullable()->constrained('admins')->nullOnDelete();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();

                $table->index(['reported_user_id', 'status']);
            });
        }

        if (!Schema::hasTable('user_blocks')) {
            Schema::create('user_blocks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('blocked_user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['user_id', 'blocked_user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_blocks');
        Schema::dropIfExists('user_reports');
    }
};
