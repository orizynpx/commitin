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

    public function mount(string $vacancy): void
    {
        // Must ensure the vacancy belongs to an event created by the current organization user
        $this->vacancy = Vacancy::whereHas('event.organizers', function($q) {
            $q->where('event_organizers.user_id', auth()->id());
        })->with('skills')->findOrFail($vacancy);

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
        return redirect()->route('organization.dashboard');
    }
}; ?>

<div class="max-w-2xl mx-auto py-8">
    <div class="mb-6">
        <a href="{{ route('organization.dashboard') }}" class="text-blue-600 hover:text-blue-800 text-sm font-semibold flex items-center gap-1">
            &larr; Kembali ke Dashboard
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <span class="text-xs font-semibold uppercase tracking-wider text-blue-600 mb-1 block">
            Event: {{ $vacancy->event->event_name }}
        </span>
        <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ __('Sunting Lowongan Divisi') }}</h1>

        <form wire:submit.prevent="update" class="space-y-6">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Divisi</label>
                <input 
                    type="text" 
                    wire:model="division" 
                    placeholder="Masukkan nama divisi (misal: Dokumentasi, Acara, Humas)" 
                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('division') border-red-500 @enderror"
                />
                @error('division')
                    <span class="text-xs text-red-500 block mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Deskripsi Tugas & Kriteria</label>
                <textarea 
                    wire:model="vacancy_description" 
                    rows="5"
                    placeholder="Jelaskan mengenai tanggung jawab divisi ini dan kriteria khusus pelamar..." 
                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('vacancy_description') border-red-500 @enderror"
                ></textarea>
                @error('vacancy_description')
                    <span class="text-xs text-red-500 block mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Status Lowongan</label>
                <select 
                    wire:model="status" 
                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('status') border-red-500 @enderror"
                >
                    <option value="OPEN">BUKA (OPEN)</option>
                    <option value="CLOSED">TUTUP (CLOSED)</option>
                </select>
                @error('status')
                    <span class="text-xs text-red-500 block mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Keahlian Yang Disyaratkan</label>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 border border-gray-100 rounded-lg p-4 bg-gray-50/50">
                    @foreach(App\Models\Skill::all() as $skill)
                        <label class="inline-flex items-center text-sm text-gray-700">
                            <input 
                                type="checkbox" 
                                wire:model="selectedSkills" 
                                value="{{ $skill->skill_id }}" 
                                class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mr-2"
                            />
                            {{ $skill->skill_name }}
                        </label>
                    @endforeach
                </div>
                @error('selectedSkills')
                    <span class="text-xs text-red-500 block mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div class="pt-4 border-t border-gray-50 flex justify-end gap-3">
                <a 
                    href="{{ route('organization.dashboard') }}" 
                    class="bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 px-5 py-2.5 rounded-lg text-sm font-semibold transition-colors"
                >
                    Batal
                </a>
                <button 
                    type="submit" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition-colors shadow-sm"
                >
                    Perbarui Lowongan
                </button>
            </div>
        </form>
    </div>
</div>
