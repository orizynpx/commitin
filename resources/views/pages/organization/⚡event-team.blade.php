<?php

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\DB;

new #[Layout('layouts.app')] class extends Component
{
    public Event $event;
    public string $searchUser = '';
    public array $searchResults = [];
    public string $selectedRole = 'manager';
    public bool $canModify = false;

    public function mount(Event $event): void
    {
        $this->event = $event->loadMissing('organizers');
        
        $myPivot = $this->event->organizers()->where('users.user_id', auth()->id())->first()?->pivot;
        
        if (!$myPivot) {
            abort(403, 'Unauthorized.');
        }

        // Only creator and owner can modify
        if (in_array($myPivot->organizer_role, ['creator', 'owner'])) {
            $this->canModify = true;
        }
    }

    private function checkAuthorization(): bool
    {
        return DB::table('event_organizers')
            ->where('event_id', $this->event->event_id)
            ->where('user_id', auth()->id())
            ->whereIn('organizer_role', ['creator', 'owner'])
            ->exists();
    }

    public function search()
    {
        if (strlen($this->searchUser) < 2) {
            $this->searchResults = [];
            return;
        }

        // Search users that are NOT already organizers of this event
        $currentOrganizerIds = DB::table('event_organizers')
            ->where('event_id', $this->event->event_id)
            ->pluck('user_id')
            ->toArray();

        $this->searchResults = User::where('name', 'like', '%' . $this->searchUser . '%')
            ->whereIn('role', ['student', 'organization'])
            ->whereNotIn('user_id', $currentOrganizerIds)
            ->take(5)
            ->get()
            ->toArray();
    }

    public function addCollaborator(string $userId)
    {
        if (!$this->checkAuthorization()) {
            session()->flash('error', 'Hanya pembuat (creator) atau pemilik (owner) event yang dapat mengelola tim.');
            return;
        }

        $this->validate([
            'selectedRole' => 'required|in:owner,manager',
        ]);

        // Re-verify that user is not already collaborator
        $isCollaborator = DB::table('event_organizers')
            ->where('event_id', $this->event->event_id)
            ->where('user_id', $userId)
            ->exists();

        if ($isCollaborator) {
            session()->flash('error', 'Pengguna sudah menjadi kolaborator.');
            return;
        }

        $this->event->organizers()->attach($userId, [
            'organizer_role' => $this->selectedRole,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->event->load('organizers');
        $this->searchUser = '';
        $this->searchResults = [];
        session()->flash('success', 'Kolaborator berhasil ditambahkan.');
    }

    public function removeCollaborator(string $userId)
    {
        if (!$this->checkAuthorization()) {
            session()->flash('error', 'Hanya pembuat (creator) atau pemilik (owner) event yang dapat mengelola tim.');
            return;
        }

        // Cannot remove yourself
        if ($userId === auth()->id()) {
            session()->flash('error', 'Anda tidak dapat menghapus diri sendiri dari tim.');
            return;
        }

        // Check target user's current role on the event via DB
        $targetRole = DB::table('event_organizers')
            ->where('event_id', $this->event->event_id)
            ->where('user_id', $userId)
            ->value('organizer_role');

        if ($targetRole === 'creator') {
            session()->flash('error', 'Pembuat (creator) event tidak dapat dihapus dari tim.');
            return;
        }

        $this->event->organizers()->detach($userId);
        $this->event->load('organizers');
        session()->flash('success', 'Kolaborator berhasil dihapus.');
    }

    public function changeRole(string $userId, string $role)
    {
        if (!$this->checkAuthorization()) {
            session()->flash('error', 'Hanya pembuat (creator) atau pemilik (owner) event yang dapat mengelola tim.');
            return;
        }

        if (!in_array($role, ['owner', 'manager'])) {
            return;
        }

        // Check target user's current role on the event via DB
        $targetRole = DB::table('event_organizers')
            ->where('event_id', $this->event->event_id)
            ->where('user_id', $userId)
            ->value('organizer_role');

        if ($targetRole === 'creator') {
            session()->flash('error', 'Peran pembuat (creator) tidak dapat diubah.');
            return;
        }

        $this->event->organizers()->updateExistingPivot($userId, [
            'organizer_role' => $role,
            'updated_at' => now(),
        ]);

        $this->event->load('organizers');
        session()->flash('success', 'Peran kolaborator berhasil diubah.');
    }
}; ?>

<div style="padding: 20px; font-family: sans-serif;">
    <div>
        <a href="{{ route('organizer.events.index') }}"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg> Kembali ke Daftar Event</a>
        <h1>Kelola Kolaborator Event</h1>
        <p>Event: <strong>{{ $event->event_name }}</strong></p>
    </div>

    @if(session()->has('success'))
        <div style="color: green; margin-bottom: 15px; font-weight: bold;">
            {{ session('success') }}
        </div>
    @endif

    @if(session()->has('error'))
        <div style="color: red; margin-bottom: 15px; font-weight: bold;">
            {{ session('error') }}
        </div>
    @endif

    <div style="display: flex; gap: 40px; margin-top: 20px;">
        <!-- Left Side: Current Team (Table) -->
        <div style="flex: 2;">
            <h2>Anggota Tim Saat Ini ({{ $event->organizers->count() }} Anggota)</h2>
            <table border="1" cellpadding="8" style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Peran Tim</th>
                        <th>Peran Platform</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($event->organizers as $org)
                        <tr>
                            <td>{{ $org->name }}</td>
                            <td>{{ $org->email }}</td>
                            <td>
                                <strong>{{ $org->pivot->organizer_role }}</strong>
                            </td>
                            <td>{{ $org->role }}</td>
                            <td>
                                @if($canModify && $org->user_id !== auth()->id() && $org->pivot->organizer_role !== 'creator')
                                    <div style="display: inline-flex; align-items: center; gap: 10px;">
                                        <select 
                                            wire:change="changeRole('{{ $org->user_id }}', $event.target.value)"
                                        >
                                            <option value="manager" {{ $org->pivot->organizer_role === 'manager' ? 'selected' : '' }}>Manager</option>
                                            <option value="owner" {{ $org->pivot->organizer_role === 'owner' ? 'selected' : '' }}>Owner</option>
                                        </select>
                                        <button 
                                            wire:click="removeCollaborator('{{ $org->user_id }}')"
                                            onclick="confirm('Apakah Anda yakin ingin menghapus kolaborator ini dari tim?') || event.stopImmediatePropagation()"
                                        >
                                            Hapus
                                        </button>
                                    </div>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Right Side: Add Collaborator -->
        <div style="flex: 1;">
            @if($canModify)
                <h2>Tambah Kolaborator Baru</h2>
                <div style="margin-top: 15px;">
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px;">Pilih Peran Tim:</label>
                        <select wire:model="selectedRole" style="width: 100%; padding: 5px;">
                            <option value="manager">Manager</option>
                            <option value="owner">Owner</option>
                        </select>
                    </div>

                    <div style="margin-bottom: 15px; position: relative;">
                        <label style="display: block; margin-bottom: 5px;">Cari Nama Kolaborator:</label>
                        <input 
                            type="text" 
                            wire:model="searchUser" 
                            wire:keyup="search"
                            placeholder="Ketik nama (min. 2 karakter)..." 
                            style="width: 100%; padding: 5px; box-sizing: border-box;"
                        />

                        @if(!empty($searchResults))
                            <div style="border: 1px solid #ccc; background: white; margin-top: 5px; max-height: 200px; overflow-y: auto;">
                                @foreach($searchResults as $res)
                                    <div style="padding: 8px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
                                        <div>
                                            <strong>{{ $res['name'] }}</strong><br/>
                                            <small>{{ $res['email'] }} ({{ $res['role'] }})</small>
                                        </div>
                                        <button 
                                            wire:click="addCollaborator('{{ $res['user_id'] }}')"
                                            style="padding: 2px 8px;"
                                        >
                                            Tambah
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @elseif(strlen($searchUser) >= 2)
                            <div style="border: 1px solid #ccc; background: white; margin-top: 5px; padding: 10px; text-align: center; color: #777;">
                                Tidak ada pengguna yang cocok.
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div style="border: 1px solid #dcdcdc; background-color: #f9f9f9; padding: 15px; text-align: center;">
                    <h3>Hak Akses Terbatas</h3>
                    <p>Anda memiliki peran <strong>Manager</strong> di kegiatan ini. Pengelolaan kolaborator dan anggota tim hanya dapat dilakukan oleh <strong>Creator</strong> atau <strong>Owner</strong> event.</p>
                </div>
            @endif
        </div>
    </div>
</div>
