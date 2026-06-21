<?php

use App\Models\Skill;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

new #[Layout('layouts.app')] class extends Component
{
    public string $search = '';
    public string $newSkillName = '';

    public ?string $editingSkillId = null;
    public string $editingSkillName = '';
    public string $editingSkillStatus = 'approved';

    public string $sortColumn = 'updated_at';
    public string $sortDirection = 'desc';

    public function mount(): void
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Anda tidak memiliki hak akses ke halaman ini.');
        }
    }

    public function createSkill(): void
    {
        $this->newSkillName = trim($this->newSkillName);

        $validated = $this->validate([
            'newSkillName' => ['required', 'string', 'max:50', 'unique:skills,skill_name'],
        ]);

        Skill::create([
            'skill_name' => $validated['newSkillName'],
            'status' => 'approved',
        ]);

        $this->newSkillName = '';
        session()->flash('status', 'Keahlian baru berhasil ditambahkan!');
    }

    public function deleteSkill(string $id): void
    {
        $skill = Skill::findOrFail($id);
        $skill->delete();
        session()->flash('status', "Keahlian \"{$skill->skill_name}\" telah dihapus secara permanen!");
    }

    public function startEdit(string $id): void
    {
        $skill = Skill::findOrFail($id);
        $this->editingSkillId = $skill->skill_id;
        $this->editingSkillName = $skill->skill_name;
        $this->editingSkillStatus = $skill->status;
    }

    public function updateSkill(): void
    {
        $this->editingSkillName = trim($this->editingSkillName);

        $validated = $this->validate([
            'editingSkillName' => ['required', 'string', 'max:50', 'unique:skills,skill_name,' . $this->editingSkillId . ',skill_id'],
            'editingSkillStatus' => ['required', 'in:approved,pending'],
        ]);

        $skill = Skill::findOrFail($this->editingSkillId);
        $skill->update([
            'skill_name' => $validated['editingSkillName'],
            'status' => $validated['editingSkillStatus'],
        ]);

        $this->reset(['editingSkillId', 'editingSkillName', 'editingSkillStatus']);
        session()->flash('status', 'Keahlian berhasil diperbarui!');
    }

    public function sortBy(string $column): void
    {
        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn = $column;
            $this->sortDirection = 'desc';
        }
    }
}; ?>

@section('title', 'Manajemen Keahlian')

<div class="space-y-8 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-on-surface tracking-tight">{{ __('Manajemen Keahlian') }}</h1>
            <p class="text-outline-variant text-sm mt-1">{{ __('Kelola daftar keahlian master, perbarui status keahlian, atau moderasi usulan keahlian baru.') }}</p>
        </div>
    </div>

    @if (session('status'))
        <div class="bg-secondary-container border border-surface-dim text-on-secondary-container rounded-xl p-4 text-sm flex items-center gap-2 shadow-sm">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="font-medium">{{ session('status') }}</span>
        </div>
    @endif

    @if ($editingSkillId)
        <div class="bg-surface-container-lowest border border-surface-dim rounded-2xl p-6 shadow-sm">
            <h3 class="text-lg font-bold text-on-surface mb-4">Ubah Keahlian</h3>
            <form wire:submit.prevent="updateSkill" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-on-surface mb-1">Nama Keahlian:</label>
                    <input type="text" wire:model="editingSkillName" required class="w-full border border-surface-dim rounded-xl px-4 py-2.5 text-sm bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-primary" />
                    @error('editingSkillName') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-on-surface mb-1">Status Keahlian:</label>
                    <select wire:model="editingSkillStatus" required class="w-full border border-surface-dim rounded-xl px-4 py-2.5 text-sm bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-primary">
                        <option value="approved">Disetujui (Approved)</option>
                        <option value="pending">Tertunda (Pending)</option>
                    </select>
                    @error('editingSkillStatus') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" wire:click="$set('editingSkillId', null)" class="px-5 py-2.5 text-sm font-semibold text-on-surface-variant hover:bg-surface-container rounded-xl transition-colors">Batal</button>
                    <button type="submit" class="bg-primary text-white hover:bg-primary-container hover:text-on-primary-container px-5 py-2.5 rounded-xl text-sm font-semibold transition-colors shadow-sm">Simpan</button>
                </div>
            </form>
        </div>
    @endif

    <div class="bg-surface-container-lowest border border-surface-dim rounded-2xl p-6 shadow-sm">
        <h3 class="text-lg font-bold text-on-surface mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            Tambah Keahlian Master
        </h3>
        <form wire:submit.prevent="createSkill" class="flex flex-col md:flex-row gap-4 items-start">
            <div class="flex-1 w-full">
                <input type="text" wire:model="newSkillName" placeholder="Masukkan nama keahlian baru..." required class="w-full border border-surface-dim rounded-xl px-4 py-2.5 text-sm bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-primary" />
                @error('newSkillName') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
            </div>
            <button type="submit" class="w-full md:w-auto bg-primary text-white hover:bg-primary-container px-6 py-2.5 rounded-xl text-sm font-semibold transition-colors shadow-sm whitespace-nowrap">Tambah Keahlian</button>
        </form>
    </div>

    <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-surface-dim p-5 flex flex-col md:flex-row gap-4 items-center justify-between">
        <div class="w-full flex flex-col md:flex-row gap-4">
            <input 
                type="text" 
                wire:model.live="search" 
                placeholder="Cari berdasarkan nama keahlian..." 
                class="w-full md:max-w-md border border-surface-dim rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary bg-surface-container-lowest"
            />
        </div>
    </div>

    @php
        $baseQuery = \App\Models\Skill::query()
            ->withCount(['users', 'vacancies']);

        if (!empty($this->search)) {
            $baseQuery->where('skill_name', 'like', '%' . $this->search . '%');
        }

        $sortCol = $this->sortColumn;
        if (!in_array($sortCol, ['skill_name', 'users_count', 'vacancies_count', 'updated_at'])) {
            $sortCol = 'updated_at';
        }
        $sortDir = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        $baseQuery->orderBy($sortCol, $sortDir);

        $pendingSkills = (clone $baseQuery)->where('status', 'pending')->get();
        $approvedSkills = (clone $baseQuery)->where('status', 'approved')->get();
    @endphp

    <div class="space-y-6">
        <h2 class="text-xl font-bold text-on-surface">Usulan Tertunda (Pending Skills)</h2>
        <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-surface-dim overflow-hidden">
            @if ($pendingSkills->isEmpty())
                <div class="p-12 text-center text-outline-variant italic text-sm">
                    Tidak ada usulan keahlian tertunda.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-surface-container border-b border-surface-dim text-xs font-bold text-on-surface uppercase tracking-wider">
                                <th class="px-6 py-4 whitespace-nowrap cursor-pointer select-none" wire:click="sortBy('skill_name')">
                                    Nama Keahlian
                                    @if ($sortColumn === 'skill_name') {{ $sortDirection === 'asc' ? '▲' : '▼' }} @endif
                                </th>
                                <th class="px-6 py-4 whitespace-nowrap cursor-pointer select-none" wire:click="sortBy('users_count')">
                                    Jumlah Pengguna
                                    @if ($sortColumn === 'users_count') {{ $sortDirection === 'asc' ? '▲' : '▼' }} @endif
                                </th>
                                <th class="px-6 py-4 whitespace-nowrap cursor-pointer select-none" wire:click="sortBy('vacancies_count')">
                                    Dibutuhkan Lowongan
                                    @if ($sortColumn === 'vacancies_count') {{ $sortDirection === 'asc' ? '▲' : '▼' }} @endif
                                </th>
                                <th class="px-6 py-4 whitespace-nowrap cursor-pointer select-none" wire:click="sortBy('updated_at')">
                                    Terakhir Diperbarui
                                    @if ($sortColumn === 'updated_at') {{ $sortDirection === 'asc' ? '▲' : '▼' }} @endif
                                </th>
                                <th class="px-6 py-4 text-right whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-dim">
                            @foreach ($pendingSkills as $skill)
                                <tr wire:key="pending-{{ $skill->skill_id }}" class="hover:bg-surface-container-low transition-colors">
                                    <td class="px-6 py-4 font-bold text-on-surface whitespace-nowrap">{{ $skill->skill_name }}</td>
                                    <td class="px-6 py-4 text-outline-variant whitespace-nowrap">{{ $skill->users_count }}</td>
                                    <td class="px-6 py-4 text-outline-variant whitespace-nowrap">{{ $skill->vacancies_count }}</td>
                                    <td class="px-6 py-4 text-outline-variant whitespace-nowrap">{{ $skill->updated_at->format('d M Y H:i') }}</td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap relative">
                                        <div x-data="{ open: false }" class="relative inline-block text-left">
                                            <button @click="open = !open" class="text-gray-500 hover:text-gray-700 focus:outline-none text-xl font-bold px-2">
                                                &#8942;
                                            </button>
                                            <div x-show="open" @click.away="open = false" style="display: none;" class="absolute right-0 mt-2 w-32 rounded-md shadow-lg bg-surface-container-lowest ring-1 ring-black ring-opacity-5 z-20 p-2 border border-surface-dim text-left">
                                                <button wire:click="startEdit('{{ $skill->skill_id }}')" @click="open = false" class="block w-full text-left px-4 py-2 text-xs hover:bg-surface-container-low text-on-surface">
                                                    Edit
                                                </button>
                                                <button onclick="confirm('Hapus keahlian ini secara permanen?') || event.stopImmediatePropagation()" wire:click="deleteSkill('{{ $skill->skill_id }}')" @click="open = false" class="block w-full text-left px-4 py-2 text-xs hover:bg-surface-container-low text-error">
                                                    Hapus
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="space-y-6">
        <h2 class="text-xl font-bold text-on-surface">Keahlian Disetujui (Approved Skills)</h2>
        <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-surface-dim overflow-hidden">
            @if ($approvedSkills->isEmpty())
                <div class="p-12 text-center text-outline-variant italic text-sm">
                    Tidak ada keahlian disetujui.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-surface-container border-b border-surface-dim text-xs font-bold text-on-surface uppercase tracking-wider">
                                <th class="px-6 py-4 whitespace-nowrap cursor-pointer select-none" wire:click="sortBy('skill_name')">
                                    Nama Keahlian
                                    @if ($sortColumn === 'skill_name') {{ $sortDirection === 'asc' ? '▲' : '▼' }} @endif
                                </th>
                                <th class="px-6 py-4 whitespace-nowrap cursor-pointer select-none" wire:click="sortBy('users_count')">
                                    Jumlah Pengguna
                                    @if ($sortColumn === 'users_count') {{ $sortDirection === 'asc' ? '▲' : '▼' }} @endif
                                </th>
                                <th class="px-6 py-4 whitespace-nowrap cursor-pointer select-none" wire:click="sortBy('vacancies_count')">
                                    Dibutuhkan Lowongan
                                    @if ($sortColumn === 'vacancies_count') {{ $sortDirection === 'asc' ? '▲' : '▼' }} @endif
                                </th>
                                <th class="px-6 py-4 whitespace-nowrap cursor-pointer select-none" wire:click="sortBy('updated_at')">
                                    Terakhir Diperbarui
                                    @if ($sortColumn === 'updated_at') {{ $sortDirection === 'asc' ? '▲' : '▼' }} @endif
                                </th>
                                <th class="px-6 py-4 text-right whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-dim">
                            @foreach ($approvedSkills as $skill)
                                <tr wire:key="approved-{{ $skill->skill_id }}" class="hover:bg-surface-container-low transition-colors">
                                    <td class="px-6 py-4 font-bold text-on-surface whitespace-nowrap">{{ $skill->skill_name }}</td>
                                    <td class="px-6 py-4 text-outline-variant whitespace-nowrap">{{ $skill->users_count }}</td>
                                    <td class="px-6 py-4 text-outline-variant whitespace-nowrap">{{ $skill->vacancies_count }}</td>
                                    <td class="px-6 py-4 text-outline-variant whitespace-nowrap">{{ $skill->updated_at->format('d M Y H:i') }}</td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap relative">
                                        <div x-data="{ open: false }" class="relative inline-block text-left">
                                            <button @click="open = !open" class="text-gray-500 hover:text-gray-700 focus:outline-none text-xl font-bold px-2">
                                                &#8942;
                                            </button>
                                            <div x-show="open" @click.away="open = false" style="display: none;" class="absolute right-0 mt-2 w-32 rounded-md shadow-lg bg-surface-container-lowest ring-1 ring-black ring-opacity-5 z-20 p-2 border border-surface-dim text-left">
                                                <button wire:click="startEdit('{{ $skill->skill_id }}')" @click="open = false" class="block w-full text-left px-4 py-2 text-xs hover:bg-surface-container-low text-on-surface">
                                                    Edit
                                                </button>
                                                <button onclick="confirm('Hapus keahlian ini secara permanen?') || event.stopImmediatePropagation()" wire:click="deleteSkill('{{ $skill->skill_id }}')" @click="open = false" class="block w-full text-left px-4 py-2 text-xs hover:bg-surface-container-low text-error">
                                                    Hapus
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
