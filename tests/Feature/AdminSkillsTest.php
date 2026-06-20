<?php

namespace Tests\Feature;

use App\Models\Skill;
use App\Models\User;
use App\Models\Vacancy;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class AdminSkillsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $nonAdmin;
    protected Skill $skillA;
    protected Skill $skillB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->nonAdmin = User::factory()->create(['role' => 'student']);

        $this->skillA = Skill::forceCreate([
            'skill_id' => (string) \Illuminate\Support\Str::ulid(),
            'skill_name' => 'Laravel 10',
            'status' => 'approved',
        ]);

        $this->skillB = Skill::forceCreate([
            'skill_id' => (string) \Illuminate\Support\Str::ulid(),
            'skill_name' => 'Laravel 11',
            'status' => 'approved',
        ]);
    }

    public function test_non_admins_cannot_access_moderation(): void
    {
        $response = $this->actingAs($this->nonAdmin)->get('/admin/skills');
        $response->assertStatus(403);
    }

    public function test_admin_can_create_skill_with_trimmed_validation(): void
    {
        // Try creating with surrounding spaces of an existing skill name
        Livewire::actingAs($this->admin)
            ->test('pages::admin.skills')
            ->set('newSkillName', '  Laravel 10  ')
            ->call('createSkill')
            ->assertHasErrors(['newSkillName' => 'unique']);

        // Create a unique one
        Livewire::actingAs($this->admin)
            ->test('pages::admin.skills')
            ->set('newSkillName', '  React JS  ')
            ->call('createSkill')
            ->assertHasNoErrors()
            ->assertSee('Keahlian baru berhasil ditambahkan!');

        $this->assertTrue(Skill::where('skill_name', 'React JS')->exists());
    }

    public function test_merge_skills_relational_integrity_and_transaction(): void
    {
        // Create an event and vacancies
        $event = Event::forceCreate([
            'event_id' => (string) \Illuminate\Support\Str::ulid(),
            'event_name' => 'IT Fest 2026',
            'description' => 'Fest description',
            'event_date' => now()->addDays(5),
            'is_official' => true,
        ]);

        $vacancy1 = Vacancy::forceCreate([
            'vacancy_id' => (string) \Illuminate\Support\Str::ulid(),
            'event_id' => $event->event_id,
            'division' => 'Backend Developer',
            'vacancy_description' => 'Laravel tasks',
            'status' => 'OPEN',
        ]);

        $vacancy2 = Vacancy::forceCreate([
            'vacancy_id' => (string) \Illuminate\Support\Str::ulid(),
            'event_id' => $event->event_id,
            'division' => 'Frontend Developer',
            'vacancy_description' => 'React tasks',
            'status' => 'OPEN',
        ]);

        // Attach user and vacancy to skillA (source)
        $user = User::factory()->create(['role' => 'student']);
        $user->skills()->attach($this->skillA->skill_id);
        $vacancy1->skills()->attach($this->skillA->skill_id);

        // Attach user and vacancy to skillB (target)
        // Note: vacancy2 is attached to target, but vacancy1 and user are only on source
        $vacancy2->skills()->attach($this->skillB->skill_id);

        Livewire::actingAs($this->admin)
            ->test('pages::admin.skills')
            ->set('mergeSourceId', $this->skillA->skill_id)
            ->set('mergeTargetId', $this->skillB->skill_id)
            ->call('mergeSkills')
            ->assertHasNoErrors()
            ->assertSee('Keahlian "Laravel 10" berhasil digabungkan ke dalam "Laravel 11"!');

        // 1. Source skill is deleted
        $this->assertFalse(Skill::where('skill_id', $this->skillA->skill_id)->exists());

        // 2. Target skill retains correct relations
        $this->assertTrue($user->skills()->where('skills.skill_id', $this->skillB->skill_id)->exists());
        $this->assertTrue($vacancy1->skills()->where('skills.skill_id', $this->skillB->skill_id)->exists());
        $this->assertTrue($vacancy2->skills()->where('skills.skill_id', $this->skillB->skill_id)->exists());

        // 3. Pivot tables are clean of source skill
        $this->assertFalse(DB::table('skill_user')->where('skill_id', $this->skillA->skill_id)->exists());
        $this->assertFalse(DB::table('skill_vacancy')->where('skill_id', $this->skillA->skill_id)->exists());
    }

    public function test_merge_validation_prevents_same_id_or_non_existent_id(): void
    {
        Livewire::actingAs($this->admin)
            ->test('pages::admin.skills')
            // Identical source and target
            ->set('mergeSourceId', $this->skillA->skill_id)
            ->set('mergeTargetId', $this->skillA->skill_id)
            ->call('mergeSkills')
            ->assertHasErrors(['mergeTargetId' => 'different']);

        Livewire::actingAs($this->admin)
            ->test('pages::admin.skills')
            // Non-existent target
            ->set('mergeSourceId', $this->skillA->skill_id)
            ->set('mergeTargetId', (string) \Illuminate\Support\Str::ulid())
            ->call('mergeSkills')
            ->assertHasErrors(['mergeTargetId' => 'exists']);
    }
}
