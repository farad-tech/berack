<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tracker_events', function (Blueprint $table) {
            $table->string('tab_id', 100)->nullable();
            $table->dropUnique(['event_id']);
            $table->unique(['site_id', 'event_id']);
            $table->index(['site_id', 'session_id', 'tab_id', 'anonymous_id', 'sequence'], 'tracker_journey_order');
        });
    }

    public function down(): void
    {
        Schema::table('tracker_events', function (Blueprint $table) {
            $table->dropIndex('tracker_journey_order');
            $table->dropUnique(['site_id', 'event_id']);
            $table->unique('event_id');
            $table->dropColumn('tab_id');
        });
    }
};
