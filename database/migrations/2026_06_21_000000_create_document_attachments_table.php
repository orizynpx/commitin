<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_attachments', function (Blueprint $table) {
            $table->ulid('document_attachment_id')->primary();
            $table->foreignUlid('vacancy_application_id')->constrained('vacancy_applications', 'vacancy_application_id')->onDelete('cascade');
            $table->string('file_url', 2083);
            $table->string('file_name');
            $table->timestamps();
        });

        $existing = DB::table('vacancy_applications')->get();
        foreach ($existing as $app) {
            if (!empty($app->file_url)) {
                $filename = basename($app->file_url);
                DB::table('document_attachments')->insert([
                    'document_attachment_id' => (string) Str::ulid(),
                    'vacancy_application_id' => $app->vacancy_application_id,
                    'file_url' => $app->file_url,
                    'file_name' => $filename,
                    'created_at' => $app->created_at ?? now(),
                    'updated_at' => $app->updated_at ?? now(),
                ]);
            }
        }

        Schema::table('vacancy_applications', function (Blueprint $table) {
            $table->string('file_url', 2083)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('vacancy_applications', function (Blueprint $table) {
            $table->string('file_url', 2083)->nullable(false)->change();
        });

        Schema::dropIfExists('document_attachments');
    }
};
