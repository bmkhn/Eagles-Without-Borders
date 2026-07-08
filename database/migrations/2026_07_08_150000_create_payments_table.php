<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->integer('year_paid'); // e.g., 2026
            $table->timestamp('date_paid')->useCurrent();
            $table->timestamps();

            // A member can only have one payment record per year
            $table->unique(['member_id', 'year_paid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
