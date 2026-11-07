<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('bio')->nullable();
            $table->string('occupation')->nullable();
            $table->string('education')->nullable();
            $table->integer('height')->nullable(); // height in cm
            $table->string('religion')->nullable();
            $table->string('relationship_goal')->nullable(); // long_term, short_term, casual, friendship, marriage
            $table->string('drinking')->nullable(); // never, socially, frequently
            $table->string('smoking')->nullable(); // never, socially, frequently
            $table->string('workout')->nullable(); // never, sometimes, everyday
            $table->string('zodiac_sign')->nullable();
            $table->string('hometown')->nullable();
            $table->json('languages')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
