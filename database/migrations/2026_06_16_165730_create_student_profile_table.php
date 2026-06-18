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
        Schema::create('student_profile', function (Blueprint $table) {
            $table->ulid('student_profile_id')->primary();
            $table->foreignUlid('user_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->string('student_id', 15)->unique();
            $table->string('faculty', 100);
            $table->string('study_program', 100);
            $table->year('entry_year')->nullable();
            $table->text('bio')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_profile');
    }
};
