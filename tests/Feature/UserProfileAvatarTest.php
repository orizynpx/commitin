<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\StudentProfile;
use App\Models\OrganizationProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class UserProfileAvatarTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_edit_profile_and_upload_avatar(): void
    {
        Storage::fake('public');

        $student = User::factory()->create(['role' => 'student', 'name' => 'Original Name']);
        $studentProfile = StudentProfile::create([
            'user_id' => $student->user_id,
            'student_id' => '12345',
            'faculty' => 'Original Faculty',
            'study_program' => 'Original Program',
            'entry_year' => 2024,
            'bio' => 'Original Bio',
        ]);

        $avatarFile = UploadedFile::fake()->image('avatar.jpg');

        Livewire::actingAs($student)
            ->test('pages::profile-edit')
            ->set('name', 'Updated Student Name')
            ->set('student_id', '54321')
            ->set('faculty', 'Updated Faculty')
            ->set('study_program', 'Updated Program')
            ->set('entry_year', 2025)
            ->set('bio', 'Updated Bio')
            ->set('avatarFile', $avatarFile)
            ->call('updateProfile')
            ->assertHasNoErrors()
            ->assertRedirect(route('profile'));

        $student->refresh();
        $this->assertEquals('Updated Student Name', $student->name);
        $this->assertEquals('54321', $student->studentProfile->student_id);
        $this->assertEquals('Updated Faculty', $student->studentProfile->faculty);
        $this->assertEquals('Updated Program', $student->studentProfile->study_program);
        $this->assertEquals(2025, $student->studentProfile->entry_year);
        $this->assertEquals('Updated Bio', $student->studentProfile->bio);

        $filename = basename($student->avatar_url);
        Storage::disk('public')->assertExists('avatars/' . $filename);
        $this->assertEquals('/storage/avatars/' . $filename, $student->avatar_url);
    }

    public function test_organization_can_edit_profile_and_upload_avatar(): void
    {
        Storage::fake('public');

        $org = User::factory()->create(['role' => 'organization', 'name' => 'Original Org']);
        $orgProfile = OrganizationProfile::create([
            'user_id' => $org->user_id,
            'organization_level' => 'study_program',
            'description' => 'Original Desc',
            'verification_status' => 'verified',
        ]);

        $avatarFile = UploadedFile::fake()->image('avatar.png');

        Livewire::actingAs($org)
            ->test('pages::profile-edit')
            ->set('name', 'Updated Org Name')
            ->set('organization_level', 'university')
            ->set('description', 'Updated Desc')
            ->set('avatarFile', $avatarFile)
            ->call('updateProfile')
            ->assertHasNoErrors()
            ->assertRedirect(route('profile'));

        $org->refresh();
        $this->assertEquals('Updated Org Name', $org->name);
        $this->assertEquals('university', $org->organizationProfile->organization_level);
        $this->assertEquals('Updated Desc', $org->organizationProfile->description);

        $filename = basename($org->avatar_url);
        Storage::disk('public')->assertExists('avatars/' . $filename);
        $this->assertEquals('/storage/avatars/' . $filename, $org->avatar_url);
    }

    public function test_avatar_upload_validation_limits(): void
    {
        Storage::fake('public');

        $student = User::factory()->create(['role' => 'student']);
        StudentProfile::create([
            'user_id' => $student->user_id,
            'student_id' => '12345',
            'faculty' => 'Faculty',
            'study_program' => 'Program',
        ]);

        // 1. Upload non-image
        $invalidFile = UploadedFile::fake()->create('document.txt', 500, 'text/plain');

        Livewire::actingAs($student)
            ->test('pages::profile-edit')
            ->set('name', 'Name')
            ->set('avatarFile', $invalidFile)
            ->call('updateProfile')
            ->assertHasErrors(['avatarFile' => 'image']);

        // 2. Upload image larger than 5MB (e.g. 6MB)
        $largeFile = UploadedFile::fake()->create('huge.png', 6000, 'image/png');

        Livewire::actingAs($student)
            ->test('pages::profile-edit')
            ->set('name', 'Name')
            ->set('avatarFile', $largeFile)
            ->call('updateProfile')
            ->assertHasErrors(['avatarFile' => 'max']);
    }

    public function test_admin_can_view_and_edit_profile(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'name' => 'Original Admin']);

        Livewire::actingAs($admin)
            ->test('pages::profile')
            ->assertSee('Original Admin');

        Livewire::actingAs($admin)
            ->test('pages::profile-edit')
            ->set('name', 'Updated Admin Name')
            ->call('updateProfile')
            ->assertHasNoErrors()
            ->assertRedirect(route('profile'));

        $admin->refresh();
        $this->assertEquals('Updated Admin Name', $admin->name);
    }
}
