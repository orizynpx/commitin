<?php

use App\Models\Skill;
use App\Models\User;
use App\Models\Vacancy;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

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
            $targetName = $target->skill_name;
            $source->delete();
        });

        $this->showMergePanel = false;
        $this->reset(['mergeSourceId', 'mergeTargetId']);
        session()->flash('status', "Keahlian \"{$sourceName}\" berhasil digabungkan ke dalam \"{$targetName}\"!");
    }
}; ?>

@section('title', 'Moderasi Keahlian')

<div style="padding: 20px; font-family: sans-serif;">
    <!-- Status Toast Alert -->
    @if (session('status'))
        <div style="color: green; font-weight: bold; margin-bottom: 20px;">
            {{ session('status') }}
        </div>
    @endif

    @php
        $pendingSkills = Skill::where('status', 'pending')->orderBy('created_at', 'desc')->get();
        $approvedSkills = Skill::where('status', 'approved')->withCount(['users', 'vacancies'])->orderBy('skill_name')->get();
        $rejectedSkills = Skill::where('status', 'rejected')->orderBy('created_at', 'desc')->get();
        $allSkills = Skill::orderBy('skill_name')->get();
    @endphp

    <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2>Moderasi Keahlian</h2>
            <p>Kelola daftar keahlian/skill master, setujui usulan mahasiswa, atau gabungkan keahlian ganda.</p>
        </div>
        <button wire:click="$set('showMergePanel', true)">
            Gabungkan Keahlian
        </button>
    </div>

    <!-- MERGE SKILLS PANEL -->
    @if ($showMergePanel)
        <div style="border: 1px solid #ccc; padding: 15px; margin-bottom: 20px; background-color: #f9f9f9;">
            <h3>Gabungkan Keahlian</h3>
            <p>Tindakan ini akan mengalihkan semua pengguna dan lowongan dari keahlian sumber ke keahlian target, lalu menghapus keahlian sumber secara permanen.</p>
            <form wire:submit.prevent="mergeSkills">
                <div style="margin-bottom: 10px;">
                    <label style="display: block; margin-bottom: 5px;">Keahlian Sumber (Yang Akan Dihapus):</label>
                    <select wire:model="mergeSourceId" required style="width: 100%; padding: 5px;">
                        <option value="">-- Pilih Keahlian Sumber --</option>
                        @foreach ($allSkills as $s)
                            <option value="{{ $s->skill_id }}">{{ $s->skill_name }} ({{ $s->status }})</option>
                        @endforeach
                    </select>
                    @error('mergeSourceId') <span style="color: red; display: block; margin-top: 5px;">{{ $message }}</span> @enderror
                </div>

                <div style="margin-bottom: 10px;">
                    <label style="display: block; margin-bottom: 5px;">Keahlian Target (Yang Akan Menyerap):</label>
                    <select wire:model="mergeTargetId" required style="width: 100%; padding: 5px;">
                        <option value="">-- Pilih Keahlian Target --</option>
                        @foreach ($allSkills as $s)
                            <option value="{{ $s->skill_id }}">{{ $s->skill_name }} ({{ $s->status }})</option>
                        @endforeach
                    </select>
                    @error('mergeTargetId') <span style="color: red; display: block; margin-top: 5px;">{{ $message }}</span> @enderror
                </div>

                <div style="margin-top: 15px;">
                    <button type="button" wire:click="$set('showMergePanel', false)" style="margin-right: 10px;">Batal</button>
                    <button type="submit">Gabungkan</button>
                </div>
            </form>
        </div>
    @endif

    <!-- EDIT SKILL PANEL -->
    @if ($editingSkillId)
        <div style="border: 1px solid #ccc; padding: 15px; margin-bottom: 20px; background-color: #f9f9f9;">
            <h3>Ubah Nama Keahlian</h3>
            <form wire:submit.prevent="updateSkill">
                <div style="margin-bottom: 10px;">
                    <label style="display: block; margin-bottom: 5px;">Nama Keahlian:</label>
                    <input type="text" wire:model="editingSkillName" required style="width: 100%; padding: 5px; box-sizing: border-box;" />
                    @error('editingSkillName') <span style="color: red; display: block; margin-top: 5px;">{{ $message }}</span> @enderror
                </div>

                <div style="margin-top: 15px;">
                    <button type="button" wire:click="$set('editingSkillId', null)" style="margin-right: 10px;">Batal</button>
                    <button type="submit">Simpan</button>
                </div>
            </form>
        </div>
    @endif

    <!-- Manual Add Skill Section -->
    <div style="border: 1px solid #ddd; padding: 15px; margin-bottom: 25px;">
        <h3>Tambah Keahlian Master</h3>
        <form wire:submit.prevent="createSkill" style="display: flex; gap: 10px; align-items: start;">
            <div style="flex: 1;">
                <input type="text" wire:model="newSkillName" placeholder="Masukkan nama keahlian baru..." required style="width: 100%; padding: 5px; box-sizing: border-box;" />
                @error('newSkillName') <span style="color: red; display: block; margin-top: 5px;">{{ $message }}</span> @enderror
            </div>
            <button type="submit">Tambah Keahlian</button>
        </form>
    </div>

    <!-- Tabs switcher -->
    <div style="margin-bottom: 20px; display: flex; gap: 20px; border-bottom: 1px solid #ccc; padding-bottom: 10px;">
        <button wire:click="$set('activeTab', 'pending')" style="font-weight: {{ $activeTab === 'pending' ? 'bold' : 'normal' }}; cursor: pointer; background: none; border: none; padding: 0;">
            Perlu Persetujuan ({{ $pendingSkills->count() }})
        </button>
        <button wire:click="$set('activeTab', 'approved')" style="font-weight: {{ $activeTab === 'approved' ? 'bold' : 'normal' }}; cursor: pointer; background: none; border: none; padding: 0;">
            Telah Disetujui ({{ $approvedSkills->count() }})
        </button>
        <button wire:click="$set('activeTab', 'rejected')" style="font-weight: {{ $activeTab === 'rejected' ? 'bold' : 'normal' }}; cursor: pointer; background: none; border: none; padding: 0;">
            Ditolak ({{ $rejectedSkills->count() }})
        </button>
    </div>

    <!-- Lists Body -->
    <div>
        @if ($activeTab === 'pending')
            @if ($pendingSkills->isEmpty())
                <p style="font-style: italic;">Tidak ada usulan keahlian baru yang menunggu persetujuan.</p>
            @else
                <table border="1" cellpadding="8" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th>Nama Keahlian</th>
                            <th>Diusulkan Pada</th>
                            <th>Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pendingSkills as $skill)
                            <tr wire:key="{{ $skill->skill_id }}">
                                <td>{{ $skill->skill_name }}</td>
                                <td>{{ $skill->created_at->format('d M Y H:i') }}</td>
                                <td>
                                    <button wire:click="approveSkill('{{ $skill->skill_id }}')" style="margin-right: 5px;">Setujui</button>
                                    <button wire:click="rejectSkill('{{ $skill->skill_id }}')" style="margin-right: 5px;">Tolak</button>
                                    <button onclick="confirm('Hapus keahlian ini secara permanen?') || event.stopImmediatePropagation()" wire:click="deleteSkill('{{ $skill->skill_id }}')">Hapus</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        @elseif ($activeTab === 'approved')
            @if ($approvedSkills->isEmpty())
                <p style="font-style: italic;">Tidak ada keahlian terdaftar.</p>
            @else
                <table border="1" cellpadding="8" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th>Nama Keahlian</th>
                            <th>Jumlah Pengguna</th>
                            <th>Dibutuhkan Vacancy</th>
                            <th>Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($approvedSkills as $skill)
                            <tr wire:key="{{ $skill->skill_id }}">
                                <td>{{ $skill->skill_name }}</td>
                                <td>{{ $skill->users_count }}</td>
                                <td>{{ $skill->vacancies_count }}</td>
                                <td>
                                    <button wire:click="startEdit('{{ $skill->skill_id }}')" style="margin-right: 5px;">Edit</button>
                                    <button wire:click="rejectSkill('{{ $skill->skill_id }}')" style="margin-right: 5px;">Tolak</button>
                                    <button onclick="confirm('Hapus keahlian ini secara permanen?') || event.stopImmediatePropagation()" wire:click="deleteSkill('{{ $skill->skill_id }}')">Hapus</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        @elseif ($activeTab === 'rejected')
            @if ($rejectedSkills->isEmpty())
                <p style="font-style: italic;">Tidak ada keahlian ditolak.</p>
            @else
                <table border="1" cellpadding="8" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th>Nama Keahlian</th>
                            <th>Ditolak Pada</th>
                            <th>Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rejectedSkills as $skill)
                            <tr wire:key="{{ $skill->skill_id }}">
                                <td>{{ $skill->skill_name }}</td>
                                <td>{{ $skill->updated_at->format('d M Y H:i') }}</td>
                                <td>
                                    <button wire:click="approveSkill('{{ $skill->skill_id }}')" style="margin-right: 5px;">Setujui</button>
                                    <button onclick="confirm('Hapus keahlian ini secara permanen?') || event.stopImmediatePropagation()" wire:click="deleteSkill('{{ $skill->skill_id }}')">Hapus</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        @endif
    </div>
</div>
