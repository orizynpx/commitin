<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use App\Models\Vacancy;
use App\Models\Skill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VacancyManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_vacancy_create_page_renders_skill_selector(): void
    {
        $org = User::factory()->create(['role' => 'organization']);
        $org->organizationProfile()->create([
            'organization_level' => 'university',
            'description' => 'Org description',
            'verification_status' => 'verified',
        ]);

        $event = Event::forceCreate([
            'event_id' => (string) \Illuminate\Support\Str::ulid(),
            'event_name' => 'Tech Summit 2026',
            'description' => 'A tech summit event',
            'event_date' => now()->addDays(10),
            'is_official' => true,
        ]);

        $event->organizers()->attach($org->user_id, ['organizer_role' => 'creator']);

        $response = $this->actingAs($org)->get(route('organizer.events.vacancies.create', $event->event_id));

        $response->assertStatus(200);
        $response->assertSeeLivewire('⚡skill-selector');
    }

    public function test_vacancy_edit_page_renders_skill_selector(): void
    {
        $org = User::factory()->create(['role' => 'organization']);
        $org->organizationProfile()->create([
            'organization_level' => 'university',
            'description' => 'Org description',
            'verification_status' => 'verified',
        ]);

        $event = Event::forceCreate([
            'event_id' => (string) \Illuminate\Support\Str::ulid(),
            'event_name' => 'Tech Summit 2026',
            'description' => 'A tech summit event',
            'event_date' => now()->addDays(10),
            'is_official' => true,
        ]);

        $event->organizers()->attach($org->user_id, ['organizer_role' => 'creator']);

        $vacancy = Vacancy::forceCreate([
            'vacancy_id' => (string) \Illuminate\Support\Str::ulid(),
            'event_id' => $event->event_id,
            'division' => 'Web Developer',
            'vacancy_description' => 'Build websites',
            'status' => 'OPEN',
        ]);

        $response = $this->actingAs($org)->get(route('organizer.vacancies.edit', $vacancy->vacancy_id));

        $response->assertStatus(200);
        $response->assertSeeLivewire('⚡skill-selector');
    }

    public function test_profile_page_renders_skill_selector(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($student)->get('/profile');

        $response->assertStatus(200);
        $response->assertSeeLivewire('⚡skill-selector');
    }
}
