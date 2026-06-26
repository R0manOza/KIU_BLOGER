<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Upvotes / downvotes on posts. `value` is +1 for an upvote and -1 for a
 * downvote; the post score is simply the SUM of these values.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('value'); // +1 = upvote, -1 = downvote
            $table->timestamps();

            // One vote per user per post (they can switch it, but not stack).
            $table->unique(['user_id', 'post_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
