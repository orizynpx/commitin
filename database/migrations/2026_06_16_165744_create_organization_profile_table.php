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
        Schema::create('organization_profile', function (Blueprint $table) {
            $table->ulid('organization_profile_id')->primary();
            $table->foreignUlid('user_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->enum('organization_level', ['study_program', 'faculty', 'university']);
            $table->text('description')->nullable();
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_profile');
    }
};
