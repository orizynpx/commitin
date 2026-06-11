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
        Schema::create('vacancy_skills', function (Blueprint $table) {
            $table->foreignUuid('vacancy_id')->constrained('vacancies', 'vacancy_id')->onDelete('cascade');
            $table->foreignUuid('skill_id')->constrained('skills', 'skill_id')->onDelete('cascade');
            $table->primary(['vacancy_id', 'skill_id']);
            // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vacancy_skills');
    }
};
