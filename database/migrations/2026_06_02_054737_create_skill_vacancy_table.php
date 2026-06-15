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
        Schema::create('skill_vacancy', function (Blueprint $table) {
            $table->foreignUlid('skill_id')->constrained('skills', 'skill_id')->onDelete('cascade');
            $table->foreignUlid('vacancy_id')->constrained('vacancies', 'vacancy_id')->onDelete('cascade');
            $table->primary(['skill_id', 'vacancy_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skill_vacancy');
    }
};
