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
        Schema::create('vacancies', function (Blueprint $table) {
            $table->ulid('vacancy_id')->primary();
            $table->foreignUlid('event_id')->constrained('events', 'event_id')->onDelete('cascade');
            $table->string('division', 50);
            $table->text('vacancy_description');
            $table->enum('status', ['OPEN', 'CLOSED']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vacancies');
    }
};
