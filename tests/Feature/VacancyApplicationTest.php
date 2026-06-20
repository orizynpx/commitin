<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use App\Models\Vacancy;
use App\Models\VacancyApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class VacancyApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_apply_to_vacancy_by_uploading_pdf(): void
    {
        Storage::fake('public');

        $student = User::factory()->create(['role' => 'student']);
        
        $event = Event::forceCreate([
            'event_id' => (string) \Illuminate\Support\Str::ulid(),
            'event_name' => 'Tech Summit 2026',
            'description' => 'A tech summit event',
            'event_date' => now()->addDays(10),
            'is_official' => true,
        ]);

        $vacancy = Vacancy::forceCreate([
            'vacancy_id' => (string) \Illuminate\Support\Str::ulid(),
            'event_id' => $event->event_id,
            'division' => 'Web Developer',
            'vacancy_description' => 'Build websites',
            'status' => 'OPEN',
        ]);

        $file = UploadedFile::fake()->create('resume.pdf', 500, 'application/pdf');

        $test = Livewire::actingAs($student)
            ->test('pages::student.vacancy-detail', ['vacancy' => $vacancy])
            ->set('file', $file)
            ->call('apply');

        $test->assertHasNoErrors()
            ->assertSee('Lamaran Anda berhasil dikirim!');

        $application = VacancyApplication::first();
        $this->assertNotNull($application);
        $this->assertEquals($student->user_id, $application->user_id);
        $this->assertEquals($vacancy->vacancy_id, $application->vacancy_id);
        $this->assertEquals('pending', $application->status);

        $filename = basename($application->file_url);
        Storage::disk('public')->assertExists('applications/' . $filename);
        $this->assertEquals('/storage/applications/' . $filename, $application->file_url);
    }

    public function test_file_upload_validation(): void
    {
        Storage::fake('public');

        $student = User::factory()->create(['role' => 'student']);

        $event = Event::forceCreate([
            'event_id' => (string) \Illuminate\Support\Str::ulid(),
            'event_name' => 'Tech Summit 2026',
            'description' => 'A tech summit event',
            'event_date' => now()->addDays(10),
            'is_official' => true,
        ]);

        $vacancy = Vacancy::forceCreate([
            'vacancy_id' => (string) \Illuminate\Support\Str::ulid(),
            'event_id' => $event->event_id,
            'division' => 'Web Developer',
            'vacancy_description' => 'Build websites',
            'status' => 'OPEN',
        ]);

        // 1. Text file instead of PDF
        $invalidFile = UploadedFile::fake()->create('resume.txt', 500, 'text/plain');

        Livewire::actingAs($student)
            ->test('pages::student.vacancy-detail', ['vacancy' => $vacancy])
            ->set('file', $invalidFile)
            ->call('apply')
            ->assertHasErrors(['file' => 'mimes']);

        // 2. PDF file but too large (11MB = 11264 KB)
        $largeFile = UploadedFile::fake()->create('huge.pdf', 11000, 'application/pdf');

        Livewire::actingAs($student)
            ->test('pages::student.vacancy-detail', ['vacancy' => $vacancy])
            ->set('file', $largeFile)
            ->call('apply')
            ->assertHasErrors(['file' => 'max']);
    }
}
