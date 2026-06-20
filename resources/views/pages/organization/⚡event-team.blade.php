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

<div class="max-w-5xl mx-auto py-8 px-4 space-y-8">
    <!-- Breadcrumb & Title Header -->
    <div class="flex flex-col gap-4">
        <a href="{{ route('organizer.events.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-semibold flex items-center gap-1 w-fit">
            &larr; Kembali ke Daftar Event
        </a>
        <div>
            <h1 class="text-3xl font-bold text-slate-900 tracking-tight">{{ __('Kelola Kolaborator Event') }}</h1>
            <p class="text-slate-500 text-sm mt-1">
                Event: <strong class="text-slate-700 font-semibold">{{ $event->event_name }}</strong>
            </p>
        </div>
    </div>

    <!-- Alert Success / Error Flashes -->
    @if(session()->has('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl p-4 text-sm flex items-center gap-2 shadow-sm">
            <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl p-4 text-sm flex items-center gap-2 shadow-sm">
            <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Main Section Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        
        <!-- Left Side: Roster Cards Grid (Span 2) -->
        <div class="lg:col-span-2 space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    Anggota Tim Saat Ini
                </h2>
                <span class="text-xs font-semibold text-slate-400 bg-slate-100 rounded-full px-2.5 py-1">
                    {{ $event->organizers->count() }} Anggota
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($event->organizers as $org)
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 flex flex-col justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <img 
                                src="https://ui-avatars.com/api/?name={{ urlencode($org->name) }}&background=f1f5f9&color=475569&bold=true" 
                                alt="{{ $org->name }}"
                                class="w-12 h-12 rounded-full object-cover bg-slate-50 border border-slate-100 flex-shrink-0"
                            />
                            <div class="min-w-0">
                                <h3 class="font-bold text-slate-900 truncate leading-snug">{{ $org->name }}</h3>
                                <p class="text-xs text-slate-400 truncate mt-0.5">{{ $org->email }}</p>
                                
                                <div class="mt-2.5 flex items-center gap-1.5">
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                                        {{ $org->pivot->organizer_role === 'creator' ? 'bg-amber-50 text-amber-700 border border-amber-100' : '' }}
                                        {{ $org->pivot->organizer_role === 'owner' ? 'bg-blue-50 text-blue-700 border border-blue-100' : '' }}
                                        {{ $org->pivot->organizer_role === 'manager' ? 'bg-slate-100 text-slate-600 border border-slate-200' : '' }}
                                    ">
                                        {{ $org->pivot->organizer_role }}
                                    </span>
                                    <span class="text-[10px] text-slate-350 bg-slate-50 rounded px-1.5 py-0.5 font-medium uppercase tracking-wider">
                                        {{ $org->role }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Action Panel -->
                        @if($canModify && $org->user_id !== auth()->id() && $org->pivot->organizer_role !== 'creator')
                            <div class="flex items-center justify-between pt-4 border-t border-slate-50">
                                <div class="flex items-center gap-2">
                                    <label class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Peran:</label>
                                    <select 
                                        wire:change="changeRole('{{ $org->user_id }}', $event.target.value)"
                                        class="border border-slate-200 rounded-lg px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 bg-white"
                                    >
                                        <option value="manager" {{ $org->pivot->organizer_role === 'manager' ? 'selected' : '' }}>Manager</option>
                                        <option value="owner" {{ $org->pivot->organizer_role === 'owner' ? 'selected' : '' }}>Owner</option>
                                    </select>
                                </div>

                                <button 
                                    wire:click="removeCollaborator('{{ $org->user_id }}')" 
                                    onclick="confirm('Apakah Anda yakin ingin menghapus kolaborator ini dari tim?') || event.stopImmediatePropagation()"
                                    class="p-1.5 text-red-500 hover:bg-red-50 hover:text-red-700 rounded-lg transition-colors"
                                    title="Hapus Kolaborator"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Right Side: Add Member Panel / Read-only Banner (Span 1) -->
        <div class="space-y-6">
            @if($canModify)
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 space-y-6">
                    <div>
                        <h2 class="text-md font-bold text-slate-900">{{ __('Tambah Kolaborator Baru') }}</h2>
                        <p class="text-xs text-slate-400 mt-1">{{ __('Cari pengguna platform untuk bergabung ke tim pengelola event ini.') }}</p>
                    </div>

                    <div class="space-y-4">
                        <!-- Role Choice -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Pilih Peran Tim</label>
                            <select 
                                wire:model="selectedRole"
                                class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                            >
                                <option value="manager">Manager</option>
                                <option value="owner">Owner</option>
                            </select>
                        </div>

                        <!-- User Search Input -->
                        <div class="relative">
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Cari Nama Kolaborator</label>
                            <input 
                                type="text" 
                                wire:model="searchUser" 
                                wire:keyup="search"
                                placeholder="Ketik nama (min. 2 karakter)..." 
                                class="w-full border border-slate-200 rounded-xl px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />

                            <!-- Floating Results Panel (Combobox style) -->
                            @if(!empty($searchResults))
                                <div class="absolute z-20 left-0 right-0 mt-2 bg-white rounded-xl shadow-lg border border-slate-100 overflow-hidden divide-y divide-slate-50">
                                    @foreach($searchResults as $res)
                                        <button 
                                            wire:click="addCollaborator('{{ $res['user_id'] }}')"
                                            class="w-full flex items-center gap-3 px-4 py-3 hover:bg-slate-50 text-left transition-colors"
                                        >
                                            <img 
                                                src="https://ui-avatars.com/api/?name={{ urlencode($res['name']) }}&background=3b82f6&color=fff&size=36&bold=true" 
                                                alt="{{ $res['name'] }}"
                                                class="w-9 h-9 rounded-full object-cover bg-slate-100"
                                            />
                                            <div class="min-w-0">
                                                <div class="text-sm font-bold text-slate-900 truncate leading-snug">{{ $res['name'] }}</div>
                                                <div class="text-xs text-slate-400 truncate">{{ $res['email'] }} &bull; <span class="uppercase text-[10px] font-semibold text-blue-600 bg-blue-50/50 rounded px-1">{{ $res['role'] }}</span></div>
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                            @elseif(strlen($searchUser) >= 2)
                                <div class="absolute z-20 left-0 right-0 mt-2 bg-white rounded-xl shadow-lg border border-slate-100 p-4 text-center text-xs text-slate-400 italic">
                                    Tidak ada pengguna yang cocok.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <!-- Read-only Banner -->
                <div class="bg-blue-50/50 border border-blue-100 rounded-2xl p-6 text-center space-y-4">
                    <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0v2m0-2h2m-2 0H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm">Hak Akses Terbatas</h3>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                            Anda memiliki peran **Manager** di kegiatan ini. Pengelolaan kolaborator dan anggota tim hanya dapat dilakukan oleh **Creator** atau **Owner** event.
                        </p>
                    </div>
                </div>
            @endif
        </div>

    </div>
</div>
