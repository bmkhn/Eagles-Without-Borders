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
        Schema::create('members', function (Blueprint $table) {
            $table->id();

            $table->foreignId('club_id')->constrained('clubs')->cascadeOnDelete();
            $table->foreignId('position_id')->constrained('positions')->cascadeOnDelete();

            $table->string('first_name');
            $table->string('middle_initial', 10)->nullable();
            $table->string('last_name');
            $table->string('suffix', 50)->nullable();
            $table->string('status', 20)->default('active');
            $table->string('slug');
            $table->string('contact_number');

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['club_id', 'position_id', 'first_name', 'last_name']);
            $table->unique(['club_id', 'slug']);
            $table->index(['club_id', 'contact_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
