<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tracker_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_id')->unique();
            $table->string('event_name')->index();
            $table->string('anonymous_id')->nullable()->index();
            $table->string('session_id')->nullable()->index();
            $table->unsignedInteger('sequence')->nullable();
            $table->text('url')->nullable();
            $table->text('path')->nullable();
            $table->string('title')->nullable();
            $table->text('previous_url')->nullable();
            $table->text('referrer')->nullable();
            $table->timestamp('occurred_at')->nullable()->index();
            $table->json('payload');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracker_events');
    }
};
