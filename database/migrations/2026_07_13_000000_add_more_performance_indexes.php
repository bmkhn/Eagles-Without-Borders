<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Members: position_id is frequently filtered but only in composite indexes
        Schema::table('members', function (Blueprint $table) {
            $table->index('position_id', 'members_position_id_index');
            $table->index('created_at', 'members_created_at_index');
            $table->index('first_name', 'members_first_name_index');
            $table->index('last_name', 'members_last_name_index');
        });

        // Clubs: name is used in WHERE clauses during CSV import resolution
        Schema::table('clubs', function (Blueprint $table) {
            $table->index('name', 'clubs_name_index');
        });

        // Regions: name is used in WHERE clauses
        Schema::table('regions', function (Blueprint $table) {
            $table->index('name', 'regions_name_index');
        });

        // Users: region_id and club_id used in scope checks
        Schema::table('users', function (Blueprint $table) {
            $table->index('region_id', 'users_region_id_index');
            $table->index('club_id', 'users_club_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropIndex('members_position_id_index');
            $table->dropIndex('members_created_at_index');
            $table->dropIndex('members_first_name_index');
            $table->dropIndex('members_last_name_index');
        });

        Schema::table('clubs', function (Blueprint $table) {
            $table->dropIndex('clubs_name_index');
        });

        Schema::table('regions', function (Blueprint $table) {
            $table->dropIndex('regions_name_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_region_id_index');
            $table->dropIndex('users_club_id_index');
        });
    }
};
