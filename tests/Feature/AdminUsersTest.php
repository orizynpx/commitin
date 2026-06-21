<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminUsersTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $student;
    protected User $blockedStudent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->student = User::factory()->create([
            'role' => 'student',
            'name' => 'Active Student',
            'email' => 'active@student.com'
        ]);
        $this->blockedStudent = User::factory()->create([
            'role' => 'student',
            'name' => 'Blocked Student',
            'email' => 'blocked@student.com',
            'blocked_at' => now(),
            'block_reason' => 'Spamming applications'
        ]);
    }

    public function test_non_admins_cannot_access_user_management(): void
    {
        $response = $this->actingAs($this->student)->get('/admin/users');
        $response->assertStatus(403);
    }

    public function test_admins_can_access_user_management(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/users');
        $response->assertStatus(200);
    }

    public function test_admin_can_search_and_filter_users(): void
    {
        $otherAdmin = User::factory()->create([
            'role' => 'admin',
            'name' => 'Second Admin User',
            'email' => 'admin2@example.com'
        ]);

        Livewire::actingAs($this->admin)
            ->test('pages::admin.users')
            ->set('search', 'Active')
            ->assertSee('Active Student')
            ->assertDontSee('Blocked Student')
            ->set('search', '')
            ->set('roleFilter', 'admin')
            ->assertSee('Second Admin User')
            ->assertDontSee('Active Student');
    }

    public function test_admin_can_block_user_with_reason(): void
    {
        Livewire::actingAs($this->admin)
            ->test('pages::admin.users')
            ->set('blockReasons.' . $this->student->user_id, 'Inappropriate behavior')
            ->call('blockUser', $this->student->user_id)
            ->assertHasNoErrors();

        $this->student->refresh();
        $this->assertNotNull($this->student->blocked_at);
        $this->assertEquals('Inappropriate behavior', $this->student->block_reason);
    }

    public function test_admin_cannot_block_user_without_reason(): void
    {
        Livewire::actingAs($this->admin)
            ->test('pages::admin.users')
            ->set('blockReasons.' . $this->student->user_id, '')
            ->call('blockUser', $this->student->user_id)
            ->assertHasErrors(['blockReasons.' . $this->student->user_id => 'required']);

        $this->student->refresh();
        $this->assertNull($this->student->blocked_at);
    }

    public function test_admin_can_unblock_user(): void
    {
        Livewire::actingAs($this->admin)
            ->test('pages::admin.users')
            ->call('unblockUser', $this->blockedStudent->user_id)
            ->assertHasNoErrors();

        $this->blockedStudent->refresh();
        $this->assertNull($this->blockedStudent->blocked_at);
        $this->assertNull($this->blockedStudent->block_reason);
    }
}
