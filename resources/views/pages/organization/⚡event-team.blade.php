<?php

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Event;
use App\Models\User;

new #[Layout('layouts.app')] class extends Component
{
    public Event $event;
    public string $searchUser = '';
    public array $searchResults = [];
    public string $selectedRole = 'manager';
    public bool $canModify = false;

    public function mount(string $event): void
    {
        $this->event = Event::with('organizers')->findOrFail($event);
        
        $myPivot = $this->event->organizers()->where('users.user_id', auth()->id())->first()?->pivot;
        
        // Only creator and owner can modify
        if ($myPivot && in_array($myPivot->organizer_role, ['creator', 'owner'])) {
            $this->canModify = true;
        }
    }

    public function search()
    {
        if (strlen($this->searchUser) < 2) {
            $this->searchResults = [];
            return;
        }

        // Search users that are NOT already organizers of this event
        $currentOrganizerIds = $this->event->organizers->pluck('user_id')->toArray();

        $this->searchResults = User::where('name', 'like', '%' . $this->searchUser . '%')
            ->whereIn('role', ['student', 'organization'])
            ->whereNotIn('user_id', $currentOrganizerIds)
            ->take(5)
            ->get()
            ->toArray();
    }

    public function addCollaborator(string $userId)
    {
        if (!$this->canModify) {
            session()->flash('error', 'Hanya pembuat (creator) atau pemilik (owner) event yang dapat mengelola tim.');
            return;
        }

        $this->validate([
            'selectedRole' => 'required|in:owner,manager',
        ]);

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
        if (!$this->canModify) {
            session()->flash('error', 'Hanya pembuat (creator) atau pemilik (owner) event yang dapat mengelola tim.');
            return;
        }

        // Cannot remove yourself if you are the owner/creator
        if ($userId === auth()->id()) {
            session()->flash('error', 'Anda tidak dapat menghapus diri sendiri dari tim.');
            return;
        }

        $this->event->organizers()->detach($userId);
        $this->event->load('organizers');
        session()->flash('success', 'Kolaborator berhasil dihapus.');
    }

    public function changeRole(string $userId, string $role)
    {
        if (!$this->canModify) {
            session()->flash('error', 'Hanya pembuat (creator) atau pemilik (owner) event yang dapat mengelola tim.');
            return;
        }

        if (!in_array($role, ['owner', 'manager'])) {
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

<div>
    <a href="{{ route('organizer.events.index') }}">Kembali ke Daftar Event</a>
    
    <h1>Kelola Tim Kolaborator Event</h1>
    <p>Kelola tim kepanitiaan dan hak akses untuk Event: <strong>{{ $event->event_name }}</strong></p>

    @if(session()->has('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    @if(session()->has('error'))
        <p style="color: red;">{{ session('error') }}</p>
    @endif

    <h3>Anggota Tim Saat Ini</h3>
    <table border="1" cellpadding="8" style="border-collapse: collapse; width: 100%;">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Role Akun</th>
                <th>Role di Event</th>
                <th>Tindakan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($event->organizers as $org)
                <tr>
                    <td>{{ $org->name }} ({{ $org->email }})</td>
                    <td>{{ $org->role }}</td>
                    <td>
                        <strong>{{ strtoupper($org->pivot->organizer_role) }}</strong>
                    </td>
                    <td>
                        @if($canModify && $org->user_id !== auth()->id() && $org->pivot->organizer_role !== 'creator')
                            <select wire:change="changeRole('{{ $org->user_id }}', $event.target.value)">
                                <option value="manager" {{ $org->pivot->organizer_role === 'manager' ? 'selected' : '' }}>Manager</option>
                                <option value="owner" {{ $org->pivot->organizer_role === 'owner' ? 'selected' : '' }}>Owner</option>
                            </select>
                            
                            <button wire:click="removeCollaborator('{{ $org->user_id }}')" onclick="return confirm('Apakah Anda yakin?')">
                                Hapus
                            </button>
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <hr />

    @if($canModify)
        <h3>Tambah Kolaborator Baru</h3>
        <p>Cari pengguna berdasarkan nama untuk ditambahkan ke tim panitia:</p>
        
        <div>
            <input 
                type="text" 
                wire:model="searchUser" 
                wire:keyup="search"
                placeholder="Ketik nama (min 2 karakter)..." 
            />
            
            <select wire:model="selectedRole">
                <option value="manager">Manager</option>
                <option value="owner">Owner</option>
            </select>
        </div>

        @if(!empty($searchResults))
            <h4>Hasil Pencarian:</h4>
            <ul>
                @foreach($searchResults as $res)
                    <li>
                        {{ $res['name'] }} ({{ $res['role'] }}) - 
                        <button wire:click="addCollaborator('{{ $res['user_id'] }}')">
                            Tambah
                        </button>
                    </li>
                @endforeach
            </ul>
        @elseif(strlen($searchUser) >= 2)
            <p>Tidak ditemukan pengguna dengan kriteria tersebut.</p>
        @endif
    @else
        <p><em>Anda memiliki hak akses Manager. Pengelolaan anggota tim dinonaktifkan.</em></p>
    @endif
</div>
