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

    public function test_student_can_apply_with_multiple_pdfs(): void
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

        $file1 = UploadedFile::fake()->create('resume.pdf', 500, 'application/pdf');
        $file2 = UploadedFile::fake()->create('portfolio.pdf', 800, 'application/pdf');

        $test = Livewire::actingAs($student)
            ->test('pages::student.vacancy-detail', ['vacancy' => $vacancy])
            ->set('files', [$file1, $file2])
            ->call('apply');

        $test->assertHasNoErrors()
            ->assertSee('Lamaran Anda berhasil dikirim!');

        $application = VacancyApplication::first();
        $this->assertNotNull($application);
        $this->assertCount(2, $application->attachments);

        $attachment1 = $application->attachments[0];
        $attachment2 = $application->attachments[1];

        $this->assertEquals('resume.pdf', $attachment1->file_name);
        $this->assertEquals('portfolio.pdf', $attachment2->file_name);

        Storage::disk('public')->assertExists('applications/' . basename($attachment1->file_url));
        Storage::disk('public')->assertExists('applications/' . basename($attachment2->file_url));

        $response1 = $this->actingAs($student)->get(route('attachments.download', $attachment1));
        $response1->assertStatus(200);

        $response2 = $this->actingAs($student)->get(route('attachments.download', $attachment2));
        $response2->assertStatus(200);
    }

    public function test_attachment_section_is_hidden_when_no_attachments(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $orgUser = User::factory()->create(['role' => 'organization']);

        $event = Event::forceCreate([
            'event_id' => (string) \Illuminate\Support\Str::ulid(),
            'event_name' => 'Tech Summit 2026',
            'description' => 'A tech summit event',
            'event_date' => now()->addDays(10),
            'is_official' => true,
        ]);
        $event->organizers()->attach($orgUser->user_id, ['organizer_role' => 'owner']);

        $vacancy = Vacancy::forceCreate([
            'vacancy_id' => (string) \Illuminate\Support\Str::ulid(),
            'event_id' => $event->event_id,
            'division' => 'Web Developer',
            'vacancy_description' => 'Build websites',
            'status' => 'OPEN',
        ]);

        $application = VacancyApplication::create([
            'user_id' => $student->user_id,
            'vacancy_id' => $vacancy->vacancy_id,
            'status' => 'pending',
            'file_url' => '',
        ]);

        Livewire::actingAs($student)
            ->test('pages::student.vacancy-detail', ['vacancy' => $vacancy])
            ->assertDontSee('Dokumen Lamaran');

        Livewire::actingAs($orgUser)
            ->test('pages::organization.application-detail', ['application' => $application])
            ->assertDontSee('Tautan Lampiran CV / Berkas Pendukung');
    }
}
