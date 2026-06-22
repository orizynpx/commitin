<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Skill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile');

        $response
            ->assertOk()
            ->assertSeeLivewire('pages::profile');
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Livewire::test('profile.update-profile-information-form')
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->call('updateProfileInformation');

        $component
            ->assertHasNoErrors()
            ->assertNoRedirect();

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Livewire::test('profile.update-profile-information-form')
            ->set('name', 'Test User')
            ->set('email', $user->email)
            ->call('updateProfileInformation');

        $component
            ->assertHasNoErrors()
            ->assertNoRedirect();

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Livewire::test('profile.delete-user-form')
            ->set('password', 'password')
            ->call('deleteUser');

        $component
            ->assertHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Livewire::test('profile.delete-user-form')
            ->set('password', 'wrong-password')
            ->call('deleteUser');

        $component
            ->assertHasErrors('password')
            ->assertNoRedirect();

        $this->assertNotNull($user->fresh());
    }

    public function test_student_can_select_and_persist_skills(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $skill = Skill::forceCreate([
            'skill_id' => (string) \Illuminate\Support\Str::ulid(),
            'skill_name' => 'PHP Laravel',
            'status' => 'approved',
        ]);

        Livewire::actingAs($student)
            ->test('pages::profile')
            ->set('selectedSkills', [$skill->skill_id])
            ->assertHasNoErrors();

        $this->assertTrue($student->skills()->where('skills.skill_id', $skill->skill_id)->exists());
    }

    public function test_suggest_new_skill_saves_as_pending_but_does_not_attach(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        Livewire::actingAs($student)
            ->test('⚡skill-selector')
            ->set('search', 'New Awesome Skill')
            ->call('suggestSkill')
            ->assertHasNoErrors();

        $skill = Skill::where('skill_name', 'New Awesome Skill')->first();
        $this->assertNotNull($skill);
        $this->assertEquals('pending', $skill->status);
        $this->assertFalse($student->skills()->where('skills.skill_id', $skill->skill_id)->exists());
    }

    public function test_suggest_already_pending_skill_does_not_duplicate(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $pendingSkill = Skill::forceCreate([
            'skill_id' => (string) \Illuminate\Support\Str::ulid(),
            'skill_name' => 'Pending Skill',
            'status' => 'pending',
        ]);

        Livewire::actingAs($student)
            ->test('⚡skill-selector')
            ->set('search', 'Pending Skill')
            ->call('suggestSkill')
            ->assertHasNoErrors();

        $this->assertEquals(1, Skill::where('skill_name', 'Pending Skill')->count());
    }
}
