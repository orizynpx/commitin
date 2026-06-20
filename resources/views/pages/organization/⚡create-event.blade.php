<?php

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Event;
use Illuminate\Support\Facades\DB;

new #[Layout('layouts.app')] class extends Component
{
    public string $event_name = '';
    public string $description = '';
    public string $event_date = '';
    public bool $is_official = false;

    public function store()
    {
        $this->validate([
            'event_name' => 'required|string|max:255',
            'description' => 'required|string',
            'event_date' => 'required|date',
            'is_official' => 'boolean',
        ]);

        DB::transaction(function() {
            $event = Event::create([
                'event_name' => $this->event_name,
                'description' => $this->description,
                'event_date' => $this->event_date,
                'is_official' => $this->is_official,
            ]);

            $event->organizers()->attach(auth()->id(), [
                'organizer_role' => 'creator',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        session()->flash('success', 'Event berhasil dibuat.');
        return redirect()->route('organizer.events.index');
    }
}; ?>

<div class="max-w-2xl mx-auto py-8">
    <div class="mb-6">
        <a href="{{ route('organizer.events.index') }}" class="text-primary hover:text-on-primary-container text-sm font-semibold flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg> Kembali ke Daftar Event
        </a>
    </div>

    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-surface-dim p-6">
        <h1 class="text-2xl font-bold text-on-surface mb-6">{{ __('Buat Kegiatan Baru') }}</h1>

        <form wire:submit.prevent="store" class="space-y-6">
            <div>
                <label class="block text-sm font-semibold text-on-surface-variant mb-2">Nama Event / Kegiatan</label>
                <input 
                    type="text" 
                    wire:model="event_name" 
                    placeholder="Masukkan nama event (misal: Rapat Kerja 2026)" 
                    class="w-full border border-surface-dim rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary @error('event_name') border-red-500 @enderror"
                />
                @error('event_name')
                    <span class="text-xs text-error block mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-on-surface-variant mb-2">Tanggal Pelaksanaan</label>
                <input 
                    type="date" 
                    wire:model="event_date" 
                    class="w-full border border-surface-dim rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary @error('event_date') border-red-500 @enderror"
                />
                @error('event_date')
                    <span class="text-xs text-error block mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-on-surface-variant mb-2">Deskripsi Kegiatan</label>
                <textarea 
                    wire:model="description" 
                    rows="5"
                    placeholder="Jelaskan mengenai agenda, latar belakang, atau tujuan kegiatan ini..." 
                    class="w-full border border-surface-dim rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary @error('description') border-red-500 @enderror"
                ></textarea>
                @error('description')
                    <span class="text-xs text-error block mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex items-start">
                <div class="flex items-center h-5">
                    <input 
                        type="checkbox" 
                        wire:model="is_official" 
                        id="is_official"
                        class="h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary"
                    />
                </div>
                <div class="ml-3 text-sm">
                    <label for="is_official" class="font-semibold text-on-surface-variant">Kegiatan Resmi Kampus</label>
                    <p class="text-outline-variant text-xs">Centang jika kegiatan ini merupakan agenda resmi/formal dari Ormawa/BEM.</p>
                </div>
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
                    Simpan Event
                </button>
            </div>
        </form>
    </div>
</div>
