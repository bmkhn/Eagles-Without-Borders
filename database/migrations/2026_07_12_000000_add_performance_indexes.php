<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Activity log: indexes for latest() sorting and description filtering
        Schema::table('activity_log', function (Blueprint $table) {
            $table->index('created_at', 'activity_log_created_at_index');
            $table->index('description', 'activity_log_description_index');
        });

        // Members: index status and position_id for filtering
        Schema::table('members', function (Blueprint $table) {
            $table->index('status', 'members_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->dropIndex('activity_log_created_at_index');
            $table->dropIndex('activity_log_description_index');
        });

        Schema::table('members', function (Blueprint $table) {
            $table->dropIndex('members_status_index');
        });
    }
};
