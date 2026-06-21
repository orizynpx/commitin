<?php

use App\Models\Skill;
use App\Models\User;
use App\Models\Vacancy;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

new #[Layout('layouts.app')] class extends Component
{
    public string $activeTab = 'pending';

    public string $newSkillName = '';

    public ?string $editingSkillId = null;
    public string $editingSkillName = '';

    public ?string $mergeSourceId = null;
    public ?string $mergeTargetId = null;
    public bool $showMergePanel = false;

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

    public function approveSkill(string $id): void
    {
        $skill = Skill::findOrFail($id);
        $skill->update(['status' => 'approved']);
        session()->flash('status', "Keahlian \"{$skill->skill_name}\" telah disetujui!");
    }

    public function rejectSkill(string $id): void
    {
        $skill = Skill::findOrFail($id);
        $skill->update(['status' => 'rejected']);
        session()->flash('status', "Keahlian \"{$skill->skill_name}\" telah ditolak!");
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
    }

    public function updateSkill(): void
    {
        $this->editingSkillName = trim($this->editingSkillName);

        $validated = $this->validate([
            'editingSkillName' => ['required', 'string', 'max:50', 'unique:skills,skill_name,' . $this->editingSkillId . ',skill_id'],
        ]);

        $skill = Skill::findOrFail($this->editingSkillId);
        $skill->update(['skill_name' => $validated['editingSkillName']]);

        $this->reset(['editingSkillId', 'editingSkillName']);
        session()->flash('status', 'Keahlian berhasil diperbarui!');
    }

    public function mergeSkills(): void
    {
        $validated = $this->validate([
            'mergeSourceId' => ['required', 'exists:skills,skill_id'],
            'mergeTargetId' => ['required', 'exists:skills,skill_id', 'different:mergeSourceId'],
        ]);

        $sourceName = '';
        $targetName = '';

        DB::transaction(function () use ($validated, &$sourceName, &$targetName) {
            $source = Skill::findOrFail($validated['mergeSourceId']);
            $target = Skill::findOrFail($validated['mergeTargetId']);

            $sourceUsers = $source->users()->pluck('users.user_id')->toArray();
            foreach ($sourceUsers as $userId) {
                if (!$target->users()->where('users.user_id', $userId)->exists()) {
                    $target->users()->attach($userId);
                }
            }
            $source->users()->detach();

            $sourceVacancies = $source->vacancies()->pluck('vacancies.vacancy_id')->toArray();
            foreach ($sourceVacancies as $vacancyId) {
                if (!$target->vacancies()->where('vacancies.vacancy_id', $vacancyId)->exists()) {
                    $target->vacancies()->attach($vacancyId);
                }
            }
            $source->vacancies()->detach();

            $sourceName = $source->skill_name;
            $targetName = $target->skill_name;
            $source->delete();
        });

        $this->showMergePanel = false;
        $this->reset(['mergeSourceId', 'mergeTargetId']);
        session()->flash('status', "Keahlian \"{$sourceName}\" berhasil digabungkan ke dalam \"{$targetName}\"!");
    }
}; ?>

@section('title', 'Moderasi Keahlian')

<div class="space-y-8 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-on-surface tracking-tight">{{ __('Moderasi Keahlian') }}</h1>
            <p class="text-outline-variant text-sm mt-1">{{ __('Kelola daftar keahlian/skill master, setujui usulan mahasiswa, atau gabungkan keahlian ganda.') }}</p>
        </div>
        <button wire:click="$set('showMergePanel', true)" class="bg-surface-container text-on-surface border border-surface-dim hover:bg-surface-container-low px-4 py-2.5 rounded-xl text-sm font-semibold transition-colors shadow-sm flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
            Gabungkan Keahlian
        </button>
    </div>

    @if (session('status'))
        <div class="bg-secondary-container border border-surface-dim text-on-secondary-container rounded-xl p-4 text-sm flex items-center gap-2 shadow-sm">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="font-medium">{{ session('status') }}</span>
        </div>
    @endif

    @php
        $pendingSkills = Skill::where('status', 'pending')->orderBy('created_at', 'desc')->get();
        $approvedSkills = Skill::where('status', 'approved')->withCount(['users', 'vacancies'])->orderBy('skill_name')->get();
        $rejectedSkills = Skill::where('status', 'rejected')->orderBy('created_at', 'desc')->get();
        $allSkills = Skill::orderBy('skill_name')->get();
    @endphp

    @if ($showMergePanel)
        <div class="bg-surface-container-lowest border border-surface-dim rounded-2xl p-6 shadow-sm">
            <h3 class="text-lg font-bold text-on-surface mb-2">Gabungkan Keahlian</h3>
            <p class="text-sm text-outline-variant mb-6">Tindakan ini akan mengalihkan semua pengguna dan lowongan dari keahlian sumber ke keahlian target, lalu menghapus keahlian sumber secara permanen.</p>
            <form wire:submit.prevent="mergeSkills" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-on-surface mb-1">Keahlian Sumber (Yang Akan Dihapus):</label>
                    <select wire:model="mergeSourceId" required class="w-full border border-surface-dim rounded-xl px-4 py-2.5 text-sm bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-primary">
                        <option value="">-- Pilih Keahlian Sumber --</option>
                        @foreach ($allSkills as $s)
                            <option value="{{ $s->skill_id }}">{{ $s->skill_name }} ({{ $s->status === 'approved' ? 'Disetujui' : ($s->status === 'pending' ? 'Tertunda' : 'Ditolak') }})</option>
                        @endforeach
                    </select>
                    @error('mergeSourceId') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-on-surface mb-1">Keahlian Target (Yang Akan Menyerap):</label>
                    <select wire:model="mergeTargetId" required class="w-full border border-surface-dim rounded-xl px-4 py-2.5 text-sm bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-primary">
                        <option value="">-- Pilih Keahlian Target --</option>
                        @foreach ($allSkills as $s)
                            <option value="{{ $s->skill_id }}">{{ $s->skill_name }} ({{ $s->status === 'approved' ? 'Disetujui' : ($s->status === 'pending' ? 'Tertunda' : 'Ditolak') }})</option>
                        @endforeach
                    </select>
                    @error('mergeTargetId') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" wire:click="$set('showMergePanel', false)" class="px-5 py-2.5 text-sm font-semibold text-on-surface-variant hover:bg-surface-container rounded-xl transition-colors">Batal</button>
                    <button type="submit" class="bg-primary text-white hover:bg-primary-container hover:text-on-primary-container px-5 py-2.5 rounded-xl text-sm font-semibold transition-colors shadow-sm">Gabungkan</button>
                </div>
            </form>
        </div>
    @endif

    @if ($editingSkillId)
        <div class="bg-surface-container-lowest border border-surface-dim rounded-2xl p-6 shadow-sm">
            <h3 class="text-lg font-bold text-on-surface mb-4">Ubah Nama Keahlian</h3>
            <form wire:submit.prevent="updateSkill" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-on-surface mb-1">Nama Keahlian:</label>
                    <input type="text" wire:model="editingSkillName" required class="w-full border border-surface-dim rounded-xl px-4 py-2.5 text-sm bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-primary" />
                    @error('editingSkillName') <span class="text-xs text-error mt-1 block">{{ $message }}</span> @enderror
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
            <button type="submit" class="w-full md:w-auto bg-primary text-white hover:bg-primary-container hover:text-on-primary-container px-6 py-2.5 rounded-xl text-sm font-semibold transition-colors shadow-sm whitespace-nowrap">Tambah Keahlian</button>
        </form>
    </div>

    <div class="flex gap-2 border-b border-surface-dim overflow-x-auto">
        <button wire:click="$set('activeTab', 'pending')" class="px-4 py-3 text-sm font-semibold transition-colors border-b-2 whitespace-nowrap flex items-center gap-2 {{ $activeTab === 'pending' ? 'border-primary text-primary' : 'border-transparent text-outline-variant hover:text-on-surface hover:border-surface-dim' }}">
            Perlu Persetujuan <span class="bg-surface-container px-2 py-0.5 rounded-full text-xs">{{ $pendingSkills->count() }}</span>
        </button>
        <button wire:click="$set('activeTab', 'approved')" class="px-4 py-3 text-sm font-semibold transition-colors border-b-2 whitespace-nowrap flex items-center gap-2 {{ $activeTab === 'approved' ? 'border-primary text-primary' : 'border-transparent text-outline-variant hover:text-on-surface hover:border-surface-dim' }}">
            Telah Disetujui <span class="bg-surface-container px-2 py-0.5 rounded-full text-xs">{{ $approvedSkills->count() }}</span>
        </button>
        <button wire:click="$set('activeTab', 'rejected')" class="px-4 py-3 text-sm font-semibold transition-colors border-b-2 whitespace-nowrap flex items-center gap-2 {{ $activeTab === 'rejected' ? 'border-primary text-primary' : 'border-transparent text-outline-variant hover:text-on-surface hover:border-surface-dim' }}">
            Ditolak <span class="bg-surface-container px-2 py-0.5 rounded-full text-xs">{{ $rejectedSkills->count() }}</span>
        </button>
    </div>

    <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-surface-dim overflow-hidden">
        @if ($activeTab === 'pending')
            @if ($pendingSkills->isEmpty())
                <div class="p-12 text-center text-outline-variant italic">
                    <svg class="w-12 h-12 mx-auto mb-3 text-surface-dim" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Tidak ada usulan keahlian baru yang menunggu persetujuan.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-container border-b border-surface-dim text-xs font-bold text-on-surface uppercase tracking-wider">
                                <th class="px-6 py-4 whitespace-nowrap">Nama Keahlian</th>
                                <th class="px-6 py-4 whitespace-nowrap">Diusulkan Pada</th>
                                <th class="px-6 py-4 text-right whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-dim text-sm">
                            @foreach ($pendingSkills as $skill)
                                <tr wire:key="{{ $skill->skill_id }}" class="hover:bg-surface-container-low transition-colors">
                                    <td class="px-6 py-4 font-bold text-on-surface whitespace-nowrap">{{ $skill->skill_name }}</td>
                                    <td class="px-6 py-4 text-outline-variant whitespace-nowrap">{{ $skill->created_at->format('d M Y H:i') }}</td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        <div class="flex justify-end gap-2">
                                            <button wire:click="approveSkill('{{ $skill->skill_id }}')" class="bg-secondary-container hover:bg-primary hover:text-white text-on-secondary-container px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors">Setujui</button>
                                            <button wire:click="rejectSkill('{{ $skill->skill_id }}')" class="bg-surface-container hover:bg-surface-container-high text-on-surface-variant px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors">Tolak</button>
                                            <button onclick="confirm('Hapus keahlian ini secara permanen?') || event.stopImmediatePropagation()" wire:click="deleteSkill('{{ $skill->skill_id }}')" class="bg-error-container hover:bg-error hover:text-on-error text-on-error-container px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors">Hapus</button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @elseif ($activeTab === 'approved')
            @if ($approvedSkills->isEmpty())
                <div class="p-12 text-center text-outline-variant italic">
                    <svg class="w-12 h-12 mx-auto mb-3 text-surface-dim" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Tidak ada keahlian terdaftar.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-container border-b border-surface-dim text-xs font-bold text-on-surface uppercase tracking-wider">
                                <th class="px-6 py-4 whitespace-nowrap">Nama Keahlian</th>
                                <th class="px-6 py-4 whitespace-nowrap">Jumlah Pengguna</th>
                                <th class="px-6 py-4 whitespace-nowrap">Dibutuhkan Lowongan</th>
                                <th class="px-6 py-4 text-right whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-dim text-sm">
                            @foreach ($approvedSkills as $skill)
                                <tr wire:key="{{ $skill->skill_id }}" class="hover:bg-surface-container-low transition-colors">
                                    <td class="px-6 py-4 font-bold text-on-surface whitespace-nowrap">{{ $skill->skill_name }}</td>
                                    <td class="px-6 py-4 text-outline-variant whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1.5 bg-surface-container px-2.5 py-1 rounded-full text-xs font-medium text-on-surface">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                            {{ $skill->users_count }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-outline-variant whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1.5 bg-surface-container px-2.5 py-1 rounded-full text-xs font-medium text-on-surface">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                            {{ $skill->vacancies_count }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        <div class="flex justify-end gap-2">
                                            <button wire:click="startEdit('{{ $skill->skill_id }}')" class="bg-surface-container hover:bg-primary hover:text-white text-on-surface-variant px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors">Edit</button>
                                            <button wire:click="rejectSkill('{{ $skill->skill_id }}')" class="bg-surface-container hover:bg-surface-dim text-on-surface-variant px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors">Tolak</button>
                                            <button onclick="confirm('Hapus keahlian ini secara permanen?') || event.stopImmediatePropagation()" wire:click="deleteSkill('{{ $skill->skill_id }}')" class="bg-error-container hover:bg-error hover:text-on-error text-on-error-container px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors">Hapus</button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @elseif ($activeTab === 'rejected')
            @if ($rejectedSkills->isEmpty())
                <div class="p-12 text-center text-outline-variant italic">
                    <svg class="w-12 h-12 mx-auto mb-3 text-surface-dim" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Tidak ada keahlian ditolak.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-container border-b border-surface-dim text-xs font-bold text-on-surface uppercase tracking-wider">
                                <th class="px-6 py-4 whitespace-nowrap">Nama Keahlian</th>
                                <th class="px-6 py-4 whitespace-nowrap">Ditolak Pada</th>
                                <th class="px-6 py-4 text-right whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-dim text-sm">
                            @foreach ($rejectedSkills as $skill)
                                <tr wire:key="{{ $skill->skill_id }}" class="hover:bg-surface-container-low transition-colors">
                                    <td class="px-6 py-4 font-bold text-on-surface whitespace-nowrap">{{ $skill->skill_name }}</td>
                                    <td class="px-6 py-4 text-outline-variant whitespace-nowrap">{{ $skill->updated_at->format('d M Y H:i') }}</td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        <div class="flex justify-end gap-2">
                                            <button wire:click="approveSkill('{{ $skill->skill_id }}')" class="bg-secondary-container hover:bg-primary hover:text-white text-on-secondary-container px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors">Setujui</button>
                                            <button onclick="confirm('Hapus keahlian ini secara permanen?') || event.stopImmediatePropagation()" wire:click="deleteSkill('{{ $skill->skill_id }}')" class="bg-error-container hover:bg-error hover:text-on-error text-on-error-container px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors">Hapus</button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @endif
    </div>
</div>
