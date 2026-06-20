<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class EventTeamTest extends TestCase
{
    use RefreshDatabase;

    protected Event $event;
    protected User $creator;
    protected User $owner;
    protected User $manager;
    protected User $nonMember;

    protected function setUp(): void
    {
        parent::setUp();

        $this->creator = User::factory()->create(['role' => 'student', 'name' => 'Creator User']);
        $this->owner = User::factory()->create(['role' => 'student', 'name' => 'Owner User']);
        $this->manager = User::factory()->create(['role' => 'student', 'name' => 'Manager User']);
        $this->nonMember = User::factory()->create(['role' => 'student', 'name' => 'Non Member User']);

        $this->event = Event::forceCreate([
            'event_id' => (string) \Illuminate\Support\Str::ulid(),
            'event_name' => 'CommitIn Tech Summit',
            'description' => 'Great tech event',
            'event_date' => now()->addDays(5),
            'is_official' => true,
        ]);

        // Attach team members
        $this->event->organizers()->attach($this->creator->user_id, ['organizer_role' => 'creator']);
        $this->event->organizers()->attach($this->owner->user_id, ['organizer_role' => 'owner']);
        $this->event->organizers()->attach($this->manager->user_id, ['organizer_role' => 'manager']);
    }

    public function test_non_collaborators_are_aborted(): void
    {
        Livewire::actingAs($this->nonMember)
            ->test('pages::organization.event-team', ['event' => $this->event])
            ->assertStatus(403);
    }

    public function test_managers_have_readonly_access(): void
    {
        Livewire::actingAs($this->manager)
            ->test('pages::organization.event-team', ['event' => $this->event])
            ->assertSet('canModify', false)
            ->assertSee('Hak Akses Terbatas');
    }

    public function test_creators_and_owners_can_modify(): void
    {
        Livewire::actingAs($this->creator)
            ->test('pages::organization.event-team', ['event' => $this->event])
            ->assertSet('canModify', true)
            ->assertDontSee('Hak Akses Terbatas');

        Livewire::actingAs($this->owner)
            ->test('pages::organization.event-team', ['event' => $this->event])
            ->assertSet('canModify', true)
            ->assertDontSee('Hak Akses Terbatas');
    }

    public function test_search_filters_collaborators_and_roles(): void
    {
        // Create matching search candidate users
        $matchingStudent = User::factory()->create(['role' => 'student', 'name' => 'Search Candidate 1']);
        $matchingOrg = User::factory()->create(['role' => 'organization', 'name' => 'Search Candidate 2']);
        $matchingAdmin = User::factory()->create(['role' => 'admin', 'name' => 'Search Candidate 3']);

        Livewire::actingAs($this->owner)
            ->test('pages::organization.event-team', ['event' => $this->event])
            ->set('searchUser', 'Search')
            ->call('search')
            ->assertSet('searchResults', function($results) use ($matchingStudent, $matchingOrg, $matchingAdmin) {
                $ids = collect($results)->pluck('user_id')->toArray();
                return in_array($matchingStudent->user_id, $ids) && 
                       in_array($matchingOrg->user_id, $ids) && 
                       !in_array($matchingAdmin->user_id, $ids);
            });
    }

    public function test_creator_cannot_be_removed_or_downgraded(): void
    {
        // Try to remove creator as owner
        Livewire::actingAs($this->owner)
            ->test('pages::organization.event-team', ['event' => $this->event])
            ->call('removeCollaborator', $this->creator->user_id)
            ->assertSee('Pembuat (creator) event tidak dapat dihapus dari tim.');

        // Verify creator still exists in event_organizers in db
        $this->assertTrue(
            DB::table('event_organizers')
                ->where('event_id', $this->event->event_id)
                ->where('user_id', $this->creator->user_id)
                ->exists()
        );

        // Try to change creator's role to manager as owner
        Livewire::actingAs($this->owner)
            ->test('pages::organization.event-team', ['event' => $this->event])
            ->call('changeRole', $this->creator->user_id, 'manager')
            ->assertSee('Peran pembuat (creator) tidak dapat diubah.');

        // Verify creator is still creator in db
        $this->assertEquals(
            'creator',
            DB::table('event_organizers')
                ->where('event_id', $this->event->event_id)
                ->where('user_id', $this->creator->user_id)
                ->value('organizer_role')
        );
    }

    public function test_owners_can_add_change_and_remove_managers(): void
    {
        $newStudent = User::factory()->create(['role' => 'student', 'name' => 'New Guy']);

        // Add
        Livewire::actingAs($this->owner)
            ->test('pages::organization.event-team', ['event' => $this->event])
            ->set('selectedRole', 'manager')
            ->call('addCollaborator', $newStudent->user_id)
            ->assertSee('Kolaborator berhasil ditambahkan.');

        $this->assertEquals(
            'manager',
            DB::table('event_organizers')
                ->where('event_id', $this->event->event_id)
                ->where('user_id', $newStudent->user_id)
                ->value('organizer_role')
        );

        // Change role to owner
        Livewire::actingAs($this->owner)
            ->test('pages::organization.event-team', ['event' => $this->event])
            ->call('changeRole', $newStudent->user_id, 'owner')
            ->assertSee('Peran kolaborator berhasil diubah.');

        $this->assertEquals(
            'owner',
            DB::table('event_organizers')
                ->where('event_id', $this->event->event_id)
                ->where('user_id', $newStudent->user_id)
                ->value('organizer_role')
        );

        // Remove collaborator
        Livewire::actingAs($this->owner)
            ->test('pages::organization.event-team', ['event' => $this->event])
            ->call('removeCollaborator', $newStudent->user_id)
            ->assertSee('Kolaborator berhasil dihapus.');

        $this->assertFalse(
            DB::table('event_organizers')
                ->where('event_id', $this->event->event_id)
                ->where('user_id', $newStudent->user_id)
                ->exists()
        );
    }
}
