<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds an opt-in flag to the follow relationship. When a follower turns this
 * on, the followed user's new events are auto-added to the follower's calendar
 * and they get notified.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('follows', function (Blueprint $table) {
            $table->boolean('follow_events')->default(false)->after('following_id');
        });
    }

    public function down(): void
    {
        Schema::table('follows', function (Blueprint $table) {
            $table->dropColumn('follow_events');
        });
    }
};
