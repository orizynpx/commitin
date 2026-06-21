<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vacancy;
use App\Models\Event;
use App\Models\VacancyApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ApplicationDownloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_download_real_upload(): void
    {
        Storage::fake('public');

        $student = User::factory()->create(['role' => 'student']);
        
        $event = Event::forceCreate([
            'event_id' => (string) Str::ulid(),
            'event_name' => 'Tech Summit 2026',
            'description' => 'A tech summit event',
            'event_date' => now()->addDays(10),
            'is_official' => true,
        ]);

        $vacancy = Vacancy::forceCreate([
            'vacancy_id' => (string) Str::ulid(),
            'event_id' => $event->event_id,
            'division' => 'Web Developer',
            'vacancy_description' => 'Build websites',
            'status' => 'OPEN',
        ]);
        
        $file = UploadedFile::fake()->create('my-resume.pdf', 500, 'application/pdf');
        $path = $file->store('applications', 'public');
        $fileUrl = Storage::url($path);

        $application = VacancyApplication::create([
            'user_id' => $student->user_id,
            'vacancy_id' => $vacancy->vacancy_id,
            'status' => 'pending',
            'file_url' => $fileUrl,
        ]);

        $response = $this->actingAs($student)->get(route('applications.download', $application));

        $response->assertStatus(200);
        Storage::disk('public')->assertExists($path);
    }

    public function test_organizer_can_download_real_upload(): void
    {
        Storage::fake('public');

        $student = User::factory()->create(['role' => 'student']);
        $orgUser = User::factory()->create(['role' => 'organization']);
        
        $event = Event::forceCreate([
            'event_id' => (string) Str::ulid(),
            'event_name' => 'Tech Summit 2026',
            'description' => 'A tech summit event',
            'event_date' => now()->addDays(10),
            'is_official' => true,
        ]);

        $event->organizers()->attach($orgUser->user_id, ['organizer_role' => 'owner']);

        $vacancy = Vacancy::forceCreate([
            'vacancy_id' => (string) Str::ulid(),
            'event_id' => $event->event_id,
            'division' => 'Web Developer',
            'vacancy_description' => 'Build websites',
            'status' => 'OPEN',
        ]);
        
        $file = UploadedFile::fake()->create('my-resume.pdf', 500, 'application/pdf');
        $path = $file->store('applications', 'public');
        $fileUrl = Storage::url($path);

        $application = VacancyApplication::create([
            'user_id' => $student->user_id,
            'vacancy_id' => $vacancy->vacancy_id,
            'status' => 'pending',
            'file_url' => $fileUrl,
        ]);

        $response = $this->actingAs($orgUser)->get(route('applications.download', $application));

        $response->assertStatus(200);
        Storage::disk('public')->assertExists($path);
    }

    public function test_unauthorized_user_cannot_download(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $otherStudent = User::factory()->create(['role' => 'student']);
        
        $event = Event::forceCreate([
            'event_id' => (string) Str::ulid(),
            'event_name' => 'Tech Summit 2026',
            'description' => 'A tech summit event',
            'event_date' => now()->addDays(10),
            'is_official' => true,
        ]);

        $vacancy = Vacancy::forceCreate([
            'vacancy_id' => (string) Str::ulid(),
            'event_id' => $event->event_id,
            'division' => 'Web Developer',
            'vacancy_description' => 'Build websites',
            'status' => 'OPEN',
        ]);
        
        $application = VacancyApplication::create([
            'user_id' => $student->user_id,
            'vacancy_id' => $vacancy->vacancy_id,
            'status' => 'pending',
            'file_url' => 'https://example.com/cv-placeholder.pdf',
        ]);

        $response = $this->actingAs($otherStudent)->get(route('applications.download', $application));

        $response->assertStatus(403);
    }

    public function test_admin_can_download_any_application(): void
    {
        Storage::fake('public');

        $student = User::factory()->create(['role' => 'student']);
        $admin = User::factory()->create(['role' => 'admin']);
        
        $event = Event::forceCreate([
            'event_id' => (string) Str::ulid(),
            'event_name' => 'Tech Summit 2026',
            'description' => 'A tech summit event',
            'event_date' => now()->addDays(10),
            'is_official' => true,
        ]);

        $vacancy = Vacancy::forceCreate([
            'vacancy_id' => (string) Str::ulid(),
            'event_id' => $event->event_id,
            'division' => 'Web Developer',
            'vacancy_description' => 'Build websites',
            'status' => 'OPEN',
        ]);
        
        $file = UploadedFile::fake()->create('my-resume.pdf', 500, 'application/pdf');
        $path = $file->store('applications', 'public');
        $fileUrl = Storage::url($path);

        $application = VacancyApplication::create([
            'user_id' => $student->user_id,
            'vacancy_id' => $vacancy->vacancy_id,
            'status' => 'pending',
            'file_url' => $fileUrl,
        ]);

        $response = $this->actingAs($admin)->get(route('applications.download', $application));

        $response->assertStatus(200);
        Storage::disk('public')->assertExists($path);
    }
}
