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

        $currentOrganizerIds = DB::table('event_organizers')
            ->where('event_id', $this->event->event_id)
            ->pluck('user_id')
            ->toArray();

        $this->searchResults = User::query()
            ->leftJoin('student_profile', 'users.user_id', '=', 'student_profile.user_id')
            ->select('users.*', 'student_profile.student_id')
            ->where(function($q) {
                $q->where('users.name', 'like', '%' . $this->searchUser . '%')
                  ->orWhere('student_profile.student_id', 'like', '%' . $this->searchUser . '%');
            })
            ->whereIn('users.role', ['student', 'organization'])
            ->whereNotIn('users.user_id', $currentOrganizerIds)
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

        if ($userId === auth()->id()) {
            session()->flash('error', 'Anda tidak dapat menghapus diri sendiri dari tim.');
            return;
        }

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

<div class="max-w-6xl mx-auto py-8">
    <div class="mb-6">
        <a href="{{ route('organizer.events.show', $event->event_id) }}" class="text-primary hover:text-on-primary-container text-sm font-semibold flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg> Kembali ke Detail Event
        </a>
        <h1 class="text-3xl font-bold text-on-surface mt-4">{{ __('Kelola Kolaborator Event') }}</h1>
        <p class="text-sm text-on-surface-variant mt-1">{{ __('Event') }}: <strong>{{ $event->event_name }}</strong></p>
    </div>

    @if(session()->has('success'))
        <div class="bg-secondary-container border border-surface-dim text-on-secondary-container rounded-xl p-4 mb-6 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session()->has('error'))
        <div class="bg-error-container border border-error-container text-on-error-container rounded-xl p-4 mb-6 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-surface-dim p-6">
                <h2 class="text-lg font-bold text-on-surface mb-4">Anggota Tim Saat Ini ({{ $event->organizers->count() }} Anggota)</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-surface-container border-b border-surface-dim text-xs font-bold text-on-surface uppercase tracking-wider">
                                <th class="px-6 py-4">Nama</th>
                                <th class="px-6 py-4">Email</th>
                                <th class="px-6 py-4">Peran Tim</th>
                                <th class="px-6 py-4">Peran Platform</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-dim">
                            @foreach($event->organizers as $org)
                                <tr class="hover:bg-surface-container-low transition-colors">
                                    <td class="px-6 py-4 font-bold text-on-surface">{{ $org->name }}</td>
                                    <td class="px-6 py-4 text-outline-variant">{{ $org->email }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold bg-surface-container text-primary">
                                            {{ $org->pivot->organizer_role === 'creator' ? 'Pembuat' : ($org->pivot->organizer_role === 'owner' ? 'Pemilik' : ($org->pivot->organizer_role === 'manager' ? 'Manajer' : $org->pivot->organizer_role)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-outline-variant">{{ $org->role === 'student' ? 'Mahasiswa' : ($org->role === 'organization' ? 'Organisasi' : $org->role) }}</td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        @if($canModify && $org->user_id !== auth()->id() && $org->pivot->organizer_role !== 'creator')
                                            <div class="inline-flex items-center gap-2">
                                                <select 
                                                    wire:change="changeRole('{{ $org->user_id }}', $event.target.value)"
                                                    class="border border-surface-dim rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-primary bg-surface-container-lowest text-on-surface"
                                                >
                                                    <option value="manager" {{ $org->pivot->organizer_role === 'manager' ? 'selected' : '' }}>Manajer</option>
                                                    <option value="owner" {{ $org->pivot->organizer_role === 'owner' ? 'selected' : '' }}>Pemilik</option>
                                                </select>
                                                <button 
                                                    wire:click="removeCollaborator('{{ $org->user_id }}')"
                                                    onclick="confirm('Apakah Anda yakin ingin menghapus kolaborator ini dari tim?') || event.stopImmediatePropagation()"
                                                    class="text-xs bg-error-container text-error px-2 py-1.5 rounded-lg font-semibold hover:bg-error hover:text-white transition-colors"
                                                >
                                                    Hapus
                                                </button>
                                            </div>
                                        @else
                                            <span class="text-outline-variant">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            @if($canModify)
                <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-surface-dim p-6">
                    <h2 class="text-lg font-bold text-on-surface mb-4">Tambah Kolaborator Baru</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-on-surface-variant mb-2">Pilih Peran Tim:</label>
                            <select wire:model="selectedRole" class="w-full border border-surface-dim rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary bg-surface-container-lowest text-on-surface">
                                <option value="manager">Manajer</option>
                                <option value="owner">Pemilik</option>
                            </select>
                        </div>

                        <div class="relative" x-data="{ open: true }">
                            <label class="block text-sm font-semibold text-on-surface-variant mb-2">Cari Nama atau NIM Kolaborator:</label>
                            <input 
                                type="text" 
                                wire:model.live="searchUser" 
                                wire:keyup="search"
                                @focus="open = true"
                                @click.away="open = false"
                                placeholder="Ketik nama atau NIM..." 
                                class="w-full border border-surface-dim rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary bg-surface-container-lowest text-on-surface"
                            />

                            @if(!empty($searchResults))
                                <div x-show="open" style="display: none;" class="absolute left-0 right-0 mt-1 max-h-60 overflow-y-auto bg-surface-container-lowest border border-surface-dim rounded-lg shadow-lg z-50 divide-y divide-surface-dim">
                                    @foreach($searchResults as $res)
                                        <button 
                                            type="button"
                                            wire:click="addCollaborator('{{ $res['user_id'] }}')"
                                            @click="open = false"
                                            class="w-full text-left px-4 py-2.5 text-sm hover:bg-surface-container transition-colors focus:outline-none flex justify-between items-center text-on-surface"
                                        >
                                            <div>
                                                <div class="font-semibold">{{ $res['name'] }}</div>
                                                <div class="text-xs text-outline-variant">{{ $res['email'] }} &bull; NIM: {{ $res['student_id'] ?: '-' }}</div>
                                            </div>
                                            <span class="text-xs text-primary font-bold">Pilih</span>
                                        </button>
                                    @endforeach
                                </div>
                            @elseif(strlen($searchUser) >= 2)
                                <div x-show="open" style="display: none;" class="absolute left-0 right-0 mt-1 p-4 bg-surface-container-lowest border border-surface-dim rounded-lg shadow-lg z-50 text-center text-xs text-outline-variant">
                                    Tidak ada pengguna yang cocok.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-surface-container-lowest border border-surface-dim rounded-xl p-6 text-center">
                    <h3 class="text-lg font-bold text-on-surface mb-2">Hak Akses Terbatas</h3>
                    <p class="text-on-surface-variant text-sm">Anda memiliki peran Manajer di kegiatan ini. Pengelolaan kolaborator dan anggota tim hanya dapat dilakukan oleh Pembuat atau Pemilik event.</p>
                </div>
            @endif
        </div>
    </div>
</div>
