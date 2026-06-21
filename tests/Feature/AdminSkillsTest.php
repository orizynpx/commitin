<?php

namespace Tests\Feature;

use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        Livewire::actingAs($this->admin)
            ->test('pages::admin.skills')
            ->set('newSkillName', '  Laravel 10  ')
            ->call('createSkill')
            ->assertHasErrors(['newSkillName' => 'unique']);

        Livewire::actingAs($this->admin)
            ->test('pages::admin.skills')
            ->set('newSkillName', '  React JS  ')
            ->call('createSkill')
            ->assertHasNoErrors()
            ->assertSee('Keahlian baru berhasil ditambahkan!');

        $this->assertTrue(Skill::where('skill_name', 'React JS')->exists());
    }

    public function test_double_table_layout_sorting_and_reject_deletes(): void
    {
        $pendingSkill = Skill::forceCreate([
            'skill_id' => (string) \Illuminate\Support\Str::ulid(),
            'skill_name' => 'Vue JS',
            'status' => 'pending',
        ]);

        $component = Livewire::actingAs($this->admin)
            ->test('pages::admin.skills')
            ->assertSee('Usulan Tertunda')
            ->assertSee('Keahlian Disetujui')
            ->assertSee('Vue JS')
            ->assertSee('Laravel 10');

        $component->call('sortBy', 'skill_name');
        $this->assertEquals('skill_name', $component->get('sortColumn'));

        $component->call('deleteSkill', $pendingSkill->skill_id);
        $this->assertFalse(Skill::where('skill_id', $pendingSkill->skill_id)->exists());
    }
}
