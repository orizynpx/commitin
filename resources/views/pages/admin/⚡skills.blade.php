<?php

use App\Models\Skill;
use App\Models\User;
use App\Models\Vacancy;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component
{
    // Tabs state
    public string $activeTab = 'pending'; // pending, approved, rejected

    // Add skill form
    public string $newSkillName = '';

    // Edit skill form
    public ?string $editingSkillId = null;
    public string $editingSkillName = '';

    // Merge skills form
    public ?string $mergeSourceId = null;
    public ?string $mergeTargetId = null;

    public function mount(): void
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Anda tidak memiliki hak akses ke halaman ini.');
        }
    }

    public function createSkill(): void
    {
        $validated = $this->validate([
            'newSkillName' => ['required', 'string', 'max:50', 'unique:skills,skill_name'],
        ]);

        Skill::create([
            'skill_name' => trim($validated['newSkillName']),
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
        $this->dispatch('open-modal', 'edit-skill-modal');
    }

    public function updateSkill(): void
    {
        $validated = $this->validate([
            'editingSkillName' => ['required', 'string', 'max:50', 'unique:skills,skill_name,' . $this->editingSkillId . ',skill_id'],
        ]);

        $skill = Skill::findOrFail($this->editingSkillId);
        $skill->update(['skill_name' => trim($validated['editingSkillName'])]);

        $this->dispatch('close-modal', 'edit-skill-modal');
        $this->reset(['editingSkillId', 'editingSkillName']);
        session()->flash('status', 'Keahlian berhasil diperbarui!');
    }

    public function mergeSkills(): void
    {
        $validated = $this->validate([
            'mergeSourceId' => ['required', 'exists:skills,skill_id'],
            'mergeTargetId' => ['required', 'exists:skills,skill_id', 'different:mergeSourceId'],
        ]);

        $source = Skill::findOrFail($validated['mergeSourceId']);
        $target = Skill::findOrFail($validated['mergeTargetId']);

        // Merge users possessing the source skill
        $sourceUsers = $source->users()->pluck('users.user_id')->toArray();
        foreach ($sourceUsers as $userId) {
            if (!$target->users()->where('users.user_id', $userId)->exists()) {
                $target->users()->attach($userId);
            }
        }
        $source->users()->detach();

        // Merge vacancies requiring the source skill
        $sourceVacancies = $source->vacancies()->pluck('vacancies.vacancy_id')->toArray();
        foreach ($sourceVacancies as $vacancyId) {
            if (!$target->vacancies()->where('vacancies.vacancy_id', $vacancyId)->exists()) {
                $target->vacancies()->attach($vacancyId);
            }
        }
        $source->vacancies()->detach();

        $sourceName = $source->skill_name;
        $source->delete();

        $this->dispatch('close-modal', 'merge-skills-modal');
        $this->reset(['mergeSourceId', 'mergeTargetId']);
        session()->flash('status', "Keahlian \"{$sourceName}\" berhasil digabungkan ke dalam \"{$target->skill_name}\"!");
    }
}; ?>

@section('title', 'Moderasi Keahlian')

<div>
    <!-- Status Toast Alert -->
    @if (session('status'))
        <div class="mb-6 p-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
            <span class="font-medium">{{ session('status') }}</span>
        </div>
    @endif

    @php
        $pendingSkills = Skill::where('status', 'pending')->orderBy('created_at', 'desc')->get();
        $approvedSkills = Skill::where('status', 'approved')->withCount(['users', 'vacancies'])->orderBy('skill_name')->get();
        $rejectedSkills = Skill::where('status', 'rejected')->orderBy('created_at', 'desc')->get();
        $allSkills = Skill::orderBy('skill_name')->get();
    @endphp

    <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-[#1e293b] mb-1">{{ __('Moderasi Keahlian') }}</h2>
            <p class="text-gray-500 text-sm">{{ __('Kelola daftar keahlian/skill master, setujui usulan mahasiswa, atau gabungkan keahlian ganda.') }}</p>
        </div>
        <button x-on:click="$dispatch('open-modal', 'merge-skills-modal')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-lg text-sm font-semibold transition-colors flex items-center gap-2 shadow-sm shadow-indigo-100">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
            {{ __('Gabungkan Keahlian') }}
        </button>
    </div>

    <!-- Manual Add Skill Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
        <h3 class="text-md font-bold text-gray-900 mb-4">{{ __('Tambah Keahlian Master') }}</h3>
        <form wire:submit.prevent="createSkill" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <x-text-input wire:model="newSkillName" placeholder="Masukkan nama keahlian baru (misal: Laravel 11)..." class="w-full text-sm" required />
                <x-input-error :messages="$errors->get('newSkillName')" class="mt-1" />
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition-colors shrink-0">
                {{ __('Tambah Keahlian') }}
            </button>
        </form>
    </div>

    <!-- Tabs switcher -->
    <div class="border-b border-gray-200 dark:border-gray-700 mb-6 flex gap-6">
        <button wire:click="$set('activeTab', 'pending')" class="pb-3 text-sm font-semibold border-b-2 transition-colors relative {{ $activeTab === 'pending' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            {{ __('Perlu Persetujuan') }}
            <span class="ml-1.5 bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full text-xs font-bold">{{ $pendingSkills->count() }}</span>
        </button>
        <button wire:click="$set('activeTab', 'approved')" class="pb-3 text-sm font-semibold border-b-2 transition-colors relative {{ $activeTab === 'approved' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            {{ __('Telah Disetujui') }}
            <span class="ml-1.5 bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full text-xs font-bold">{{ $approvedSkills->count() }}</span>
        </button>
        <button wire:click="$set('activeTab', 'rejected')" class="pb-3 text-sm font-semibold border-b-2 transition-colors relative {{ $activeTab === 'rejected' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            {{ __('Ditolak') }}
            <span class="ml-1.5 bg-red-50 text-red-700 px-2 py-0.5 rounded-full text-xs font-bold">{{ $rejectedSkills->count() }}</span>
        </button>
    </div>

    <!-- Lists Body -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            @if ($activeTab === 'pending')
                @if ($pendingSkills->isEmpty())
                    <p class="p-8 text-center text-sm text-gray-500 italic">{{ __('Tidak ada usulan keahlian baru yang menunggu persetujuan.') }}</p>
                @else
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-gray-100">
                                <th class="px-6 py-4 font-semibold">{{ __('Nama Keahlian') }}</th>
                                <th class="px-6 py-4 font-semibold">{{ __('Diusulkan Pada') }}</th>
                                <th class="px-6 py-4 font-semibold text-right">{{ __('Tindakan') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @foreach ($pendingSkills as $skill)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $skill->skill_name }}</td>
                                    <td class="px-6 py-4 text-gray-500">{{ $skill->created_at->format('d M Y H:i') }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <button wire:click="approveSkill('{{ $skill->skill_id }}')" class="bg-emerald-500 hover:bg-emerald-600 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors">
                                                {{ __('Setujui') }}
                                            </button>
                                            <button wire:click="rejectSkill('{{ $skill->skill_id }}')" class="bg-amber-500 hover:bg-amber-600 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors">
                                                {{ __('Tolak') }}
                                            </button>
                                            <button onclick="confirm('Hapus keahlian ini secara permanen?') || event.stopImmediatePropagation()" wire:click="deleteSkill('{{ $skill->skill_id }}')" class="text-red-500 hover:text-red-700 p-1.5">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            @elseif ($activeTab === 'approved')
                @if ($approvedSkills->isEmpty())
                    <p class="p-8 text-center text-sm text-gray-500 italic">{{ __('Tidak ada keahlian terdaftar.') }}</p>
                @else
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-gray-100">
                                <th class="px-6 py-4 font-semibold">{{ __('Nama Keahlian') }}</th>
                                <th class="px-6 py-4 font-semibold">{{ __('Jumlah Pengguna') }}</th>
                                <th class="px-6 py-4 font-semibold">{{ __('Dibutuhkan Vacancy') }}</th>
                                <th class="px-6 py-4 font-semibold text-right">{{ __('Tindakan') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @foreach ($approvedSkills as $skill)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $skill->skill_name }}</td>
                                    <td class="px-6 py-4 text-gray-500">{{ $skill->users_count }}</td>
                                    <td class="px-6 py-4 text-gray-500">{{ $skill->vacancies_count }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <button wire:click="startEdit('{{ $skill->skill_id }}')" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors">
                                                {{ __('Edit') }}
                                            </button>
                                            <button wire:click="rejectSkill('{{ $skill->skill_id }}')" class="bg-amber-500 hover:bg-amber-600 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors">
                                                {{ __('Tolak') }}
                                            </button>
                                            <button onclick="confirm('Hapus keahlian ini secara permanen?') || event.stopImmediatePropagation()" wire:click="deleteSkill('{{ $skill->skill_id }}')" class="text-red-500 hover:text-red-700 p-1.5">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            @elseif ($activeTab === 'rejected')
                @if ($rejectedSkills->isEmpty())
                    <p class="p-8 text-center text-sm text-gray-500 italic">{{ __('Tidak ada keahlian ditolak.') }}</p>
                @else
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-gray-100">
                                <th class="px-6 py-4 font-semibold">{{ __('Nama Keahlian') }}</th>
                                <th class="px-6 py-4 font-semibold">{{ __('Ditolak Pada') }}</th>
                                <th class="px-6 py-4 font-semibold text-right">{{ __('Tindakan') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @foreach ($rejectedSkills as $skill)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $skill->skill_name }}</td>
                                    <td class="px-6 py-4 text-gray-500">{{ $skill->updated_at->format('d M Y H:i') }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <button wire:click="approveSkill('{{ $skill->skill_id }}')" class="bg-emerald-500 hover:bg-emerald-600 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors">
                                                {{ __('Setujui') }}
                                            </button>
                                            <button onclick="confirm('Hapus keahlian ini secara permanen?') || event.stopImmediatePropagation()" wire:click="deleteSkill('{{ $skill->skill_id }}')" class="text-red-500 hover:text-red-700 p-1.5">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            @endif
        </div>
    </div>

    <!-- RENAME/EDIT SKILL MODAL -->
    <x-modal name="edit-skill-modal">
        <form wire:submit.prevent="updateSkill" class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Ubah Nama Keahlian') }}
            </h2>

            <div class="mt-4">
                <x-input-label for="edit_skill_name" :value="__('Nama Keahlian')" />
                <x-text-input wire:model="editingSkillName" id="edit_skill_name" class="block mt-1 w-full" type="text" required />
                <x-input-error :messages="$errors->get('editingSkillName')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close-modal', 'edit-skill-modal')">
                    {{ __('Batal') }}
                </x-secondary-button>
                <x-primary-button>
                    {{ __('Simpan') }}
                </x-primary-button>
            </div>
        </form>
    </x-modal>

    <!-- MERGE SKILLS MODAL -->
    <x-modal name="merge-skills-modal">
        <form wire:submit.prevent="mergeSkills" class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                {{ __('Gabungkan Keahlian') }}
            </h2>
            <p class="text-sm text-gray-500 mb-4">
                {{ __('Tindakan ini akan mengalihkan semua pengguna dan lowongan dari keahlian sumber ke keahlian target, lalu menghapus keahlian sumber secara permanen.') }}
            </p>

            <div class="mt-4">
                <x-input-label for="merge_source" :value="__('Keahlian Sumber (Yang Akan Dihapus)')" />
                <select wire:model="mergeSourceId" id="merge_source" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 text-sm" required>
                    <option value="">-- Pilih Keahlian Sumber --</option>
                    @foreach ($allSkills as $s)
                        <option value="{{ $s->skill_id }}">{{ $s->skill_name }} ({{ $s->status }})</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('mergeSourceId')" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-input-label for="merge_target" :value="__('Keahlian Target (Yang Akan Menyerap)')" />
                <select wire:model="mergeTargetId" id="merge_target" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 text-sm" required>
                    <option value="">-- Pilih Keahlian Target --</option>
                    @foreach ($allSkills as $s)
                        <option value="{{ $s->skill_id }}">{{ $s->skill_name }} ({{ $s->status }})</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('mergeTargetId')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close-modal', 'merge-skills-modal')">
                    {{ __('Batal') }}
                </x-secondary-button>
                <x-primary-button class="bg-indigo-600 hover:bg-indigo-700">
                    {{ __('Gabungkan') }}
                </x-primary-button>
            </div>
        </form>
    </x-modal>
</div>
