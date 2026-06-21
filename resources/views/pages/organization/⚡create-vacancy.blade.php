<?php

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Event;
use App\Models\Vacancy;
use App\Models\Skill;
use Illuminate\Support\Facades\DB;

new #[Layout('layouts.app')] class extends Component
{
    public Event $event;
    public string $division = '';
    public string $vacancy_description = '';
    public string $status = 'OPEN';
    public array $selectedSkills = [];
    public string $suggestedSkillName = '';

    public function mount(Event $event): void
    {
        $hasAccess = $event->organizers()->where('users.user_id', auth()->id())->exists();
        if (!$hasAccess) {
            abort(403, 'Unauthorized.');
        }
        $this->event = $event;
    }

    public function store()
    {
        $this->validate([
            'division' => 'required|string|max:50',
            'vacancy_description' => 'required|string',
            'status' => 'required|in:OPEN,CLOSED',
            'selectedSkills' => 'array',
        ]);

        DB::transaction(function() {
            $vacancy = Vacancy::create([
                'event_id' => $this->event->event_id,
                'division' => $this->division,
                'vacancy_description' => $this->vacancy_description,
                'status' => $this->status,
            ]);

            if (!empty($this->selectedSkills)) {
                $vacancy->skills()->sync($this->selectedSkills);
            }
        });

        session()->flash('success', 'Lowongan divisi berhasil dibuka.');
        return redirect()->route('organizer.events.index');
    }

    public function openSuggestSkillModal(): void
    {
        $this->suggestedSkillName = '';
        $this->dispatch('open-modal', 'suggest-skill-modal');
    }

    public function suggestSkill(): void
    {
        $validated = $this->validate([
            'suggestedSkillName' => ['required', 'string', 'max:50', 'unique:skills,skill_name'],
        ], [
            'suggestedSkillName.unique' => 'Keahlian ini sudah terdaftar.',
        ]);

        Skill::create([
            'skill_name' => trim($validated['suggestedSkillName']),
            'status' => 'pending',
        ]);

        $this->dispatch('close-modal', 'suggest-skill-modal');
        $this->suggestedSkillName = '';
        session()->flash('success', 'Usulan keahlian baru berhasil diajukan dan menunggu persetujuan admin!');
    }
}; ?>

<div class="max-w-2xl mx-auto py-8">
    <div class="mb-6">
        <a href="{{ route('organizer.events.index') }}" class="text-primary hover:text-on-primary-container text-sm font-semibold flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg> Kembali ke Daftar Event
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 p-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-surface-dim p-6">
        <span class="text-xs font-semibold uppercase tracking-wider text-primary mb-1 block">
            Event: {{ $event->event_name }}
        </span>
        <h1 class="text-2xl font-bold text-on-surface mb-6">{{ __('Buka Lowongan Divisi') }}</h1>

        <form wire:submit.prevent="store" class="space-y-6">
            <div>
                <label class="block text-sm font-semibold text-on-surface-variant mb-2">Nama Divisi</label>
                <input 
                    type="text" 
                    wire:model="division" 
                    placeholder="Masukkan nama divisi (misal: Dokumentasi, Acara, Humas)" 
                    class="w-full border border-surface-dim rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary @error('division') border-red-500 @enderror"
                />
                @error('division')
                    <span class="text-xs text-error block mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-on-surface-variant mb-2">Deskripsi Tugas & Kriteria</label>
                <textarea 
                    wire:model="vacancy_description" 
                    rows="5"
                    placeholder="Jelaskan mengenai tanggung jawab divisi ini dan kriteria khusus pelamar..." 
                    class="w-full border border-surface-dim rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary @error('vacancy_description') border-red-500 @enderror"
                ></textarea>
                @error('vacancy_description')
                    <span class="text-xs text-error block mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-on-surface-variant mb-2">Status Lowongan</label>
                <select 
                    wire:model="status" 
                    class="w-full border border-surface-dim rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary @error('status') border-red-500 @enderror"
                >
                    <option value="OPEN">BUKA (OPEN)</option>
                    <option value="CLOSED">TUTUP (CLOSED)</option>
                </select>
                @error('status')
                    <span class="text-xs text-error block mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-on-surface-variant mb-2">Keahlian Yang Disyaratkan</label>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 border border-surface-dim rounded-lg p-4 bg-surface-container-low">
                    @foreach(App\Models\Skill::where('status', 'approved')->get() as $skill)
                        <label class="inline-flex items-center text-sm text-on-surface-variant">
                            <input 
                                type="checkbox" 
                                wire:model="selectedSkills" 
                                value="{{ $skill->skill_id }}" 
                                class="h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary mr-2"
                            />
                            {{ $skill->skill_name }}
                        </label>
                    @endforeach
                </div>
                <button type="button" wire:click="openSuggestSkillModal" class="text-xs text-primary hover:underline mt-2 block">Usulkan Keahlian Baru</button>
                @error('selectedSkills')
                    <span class="text-xs text-error block mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div class="pt-4 border-t border-surface-dim flex justify-end gap-3">
                <a 
                    href="{{ route('organizer.events.index') }}" 
                    class="bg-surface-container-lowest border border-surface-dim text-on-surface-variant hover:bg-surface-container px-5 py-2.5 rounded-lg text-sm font-semibold transition-colors"
                >
                    Batal
                </a>
                <button 
                    type="submit" 
                    class="bg-primary hover:bg-primary-container text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition-colors shadow-sm"
                >
                    Buka Lowongan
                </button>
            </div>
        </form>
    </div>

    <x-modal name="suggest-skill-modal">
        <form wire:submit.prevent="suggestSkill" class="p-6">
            <h2 class="text-lg font-medium text-on-surface dark:text-gray-100">
                Usulkan Keahlian Baru
            </h2>

            <div class="mt-4">
                <x-input-label for="suggested_skill_name" :value="__('Nama Keahlian')" />
                <x-text-input wire:model="suggestedSkillName" id="suggested_skill_name" placeholder="Misal: Python" class="block mt-1 w-full" type="text" required />
                <x-input-error :messages="$errors->get('suggestedSkillName')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close-modal', 'suggest-skill-modal')">
                    {{ __('Batal') }}
                </x-secondary-button>

                <x-primary-button>
                    {{ __('Usulkan') }}
                </x-primary-button>
            </div>
        </form>
    </x-modal>
</div>
