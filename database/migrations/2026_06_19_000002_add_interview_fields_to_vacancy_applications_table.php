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
        Schema::table('vacancy_applications', function (Blueprint $table) {
            $table->timestamp('interview_scheduled_at')->nullable()->after('file_url');
            $table->string('interview_format')->nullable()->after('interview_scheduled_at'); // e.g., online, offline
            $table->string('interview_location', 2083)->nullable()->after('interview_format'); // zoom/meet link or room location
            $table->text('feedback')->nullable()->after('interview_location'); // interviewer notes / rejection reasons
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vacancy_applications', function (Blueprint $table) {
            $table->dropColumn([
                'interview_scheduled_at',
                'interview_format',
                'interview_location',
                'feedback',
            ]);
        });
    }
};
