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
        Schema::create('event_organizers', function (Blueprint $table) {
            $table->foreignUlid('event_id')->constrained('events', 'event_id')->onDelete('cascade');
            $table->foreignUlid('user_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->primary(['event_id', 'user_id']);
            $table->enum('organizer_role', ['creator', 'owner', 'manager']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_organizers');
    }
};
