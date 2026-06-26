<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Self-referential Many-to-Many: a user follows many users and is followed
 * by many users. Both foreign keys point back to the same `users` table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('follows', function (Blueprint $table) {
            $table->id();
            // The user who clicks "Follow".
            $table->foreignId('follower_id')->constrained('users')->cascadeOnDelete();
            // The user being followed.
            $table->foreignId('following_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            // A user can only follow another user once.
            $table->unique(['follower_id', 'following_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follows');
    }
};
