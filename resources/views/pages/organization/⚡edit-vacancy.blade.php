<?php

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Vacancy;
use App\Models\Skill;
use Illuminate\Support\Facades\DB;

new #[Layout('layouts.app')] class extends Component
{
    public Vacancy $vacancy;
    public string $division = '';
    public string $vacancy_description = '';
    public string $status = 'OPEN';
    public array $selectedSkills = [];

    public function mount(Vacancy $vacancy): void
    {
        $hasAccess = $vacancy->event->organizers()->where('users.user_id', auth()->id())->exists();
        if (!$hasAccess) {
            abort(403, 'Unauthorized.');
        }

        $this->vacancy = $vacancy->loadMissing('skills');

        $this->division = $this->vacancy->division;
        $this->vacancy_description = $this->vacancy->vacancy_description;
        $this->status = $this->vacancy->status;
        $this->selectedSkills = $this->vacancy->skills->pluck('skill_id')->toArray();
    }

    public function update()
    {
        $this->validate([
            'division' => 'required|string|max:50',
            'vacancy_description' => 'required|string',
            'status' => 'required|in:OPEN,CLOSED',
            'selectedSkills' => 'array',
        ]);

        DB::transaction(function() {
            $this->vacancy->update([
                'division' => $this->division,
                'vacancy_description' => $this->vacancy_description,
                'status' => $this->status,
            ]);

            $this->vacancy->skills()->sync($this->selectedSkills);
        });

        session()->flash('success', 'Lowongan divisi berhasil diperbarui.');
        return redirect()->route('organizer.dashboard');
    }
}; ?>

<div class="max-w-2xl mx-auto py-8">
    <div class="mb-6">
        <a href="{{ route('organizer.dashboard') }}" class="text-primary hover:text-on-primary-container text-sm font-semibold flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg> Kembali ke Dashboard
        </a>
    </div>

    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-surface-dim p-6">
        <span class="text-xs font-semibold uppercase tracking-wider text-primary mb-1 block">
            Event: {{ $vacancy->event->event_name }}
        </span>
        <h1 class="text-2xl font-bold text-on-surface mb-6">{{ __('Sunting Lowongan Divisi') }}</h1>

        <form wire:submit.prevent="update" class="space-y-6">
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
                <livewire:⚡skill-selector wire:model="selectedSkills" />
                @error('selectedSkills')
                    <span class="text-xs text-error block mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div class="pt-4 border-t border-surface-dim flex justify-end gap-3">
                <a 
                    href="{{ route('organizer.dashboard') }}" 
                    class="bg-surface-container-lowest border border-surface-dim text-on-surface-variant hover:bg-surface-container px-5 py-2.5 rounded-lg text-sm font-semibold transition-colors"
                >
                    Batal
                </a>
                <button 
                    type="submit" 
                    class="bg-primary hover:bg-primary-container text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition-colors shadow-sm"
                >
                    Perbarui Lowongan
                </button>
            </div>
        </form>
    </div>
</div>
