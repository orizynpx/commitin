<?php

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Event;

new #[Layout('layouts.app')] class extends Component
{
    public Event $event;
    public string $event_name = '';
    public string $description = '';
    public string $event_date = '';
    public bool $is_official = false;

    public function mount(Event $event): void
    {
        $this->event = $event;
        
        $myPivot = $this->event->organizers()->where('users.user_id', auth()->id())->first()?->pivot;
        if (!$myPivot || !in_array($myPivot->organizer_role, ['creator', 'owner'])) {
            abort(403, 'Hanya pembuat (creator) atau pemilik (owner) yang dapat mengedit event ini.');
        }

        $this->event_name = $this->event->event_name;
        $this->description = $this->event->description;
        $this->event_date = $this->event->event_date ? $this->event->event_date->format('Y-m-d') : '';
        $this->is_official = (bool)$this->event->is_official;
    }

    public function update()
    {
        $this->validate([
            'event_name' => 'required|string|max:255',
            'description' => 'required|string',
            'event_date' => 'required|date',
            'is_official' => 'boolean',
        ]);

        $this->event->update([
            'event_name' => $this->event_name,
            'description' => $this->description,
            'event_date' => $this->event_date,
            'is_official' => $this->is_official,
        ]);

        session()->flash('success', 'Event berhasil diperbarui.');
        return redirect()->route('organizer.events.index');
    }
}; ?>

<div class="max-w-2xl mx-auto py-8">
    <div class="mb-6">
        <a href="{{ route('organizer.events.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-semibold flex items-center gap-1">
            &larr; Kembali ke Daftar Event
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ __('Sunting Kegiatan') }}</h1>

        <form wire:submit.prevent="update" class="space-y-6">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Event / Kegiatan</label>
                <input 
                    type="text" 
                    wire:model="event_name" 
                    placeholder="Masukkan nama event" 
                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('event_name') border-red-500 @enderror"
                />
                @error('event_name')
                    <span class="text-xs text-red-500 block mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Pelaksanaan</label>
                <input 
                    type="date" 
                    wire:model="event_date" 
                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('event_date') border-red-500 @enderror"
                />
                @error('event_date')
                    <span class="text-xs text-red-500 block mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Deskripsi Kegiatan</label>
                <textarea 
                    wire:model="description" 
                    rows="5"
                    placeholder="Jelaskan mengenai agenda, latar belakang, atau tujuan kegiatan ini..." 
                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('description') border-red-500 @enderror"
                ></textarea>
                @error('description')
                    <span class="text-xs text-red-500 block mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex items-start">
                <div class="flex items-center h-5">
                    <input 
                        type="checkbox" 
                        wire:model="is_official" 
                        id="is_official"
                        class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                    />
                </div>
                <div class="ml-3 text-sm">
                    <label for="is_official" class="font-semibold text-gray-700">Kegiatan Resmi Kampus</label>
                    <p class="text-gray-500 text-xs">Centang jika kegiatan ini merupakan agenda resmi/formal dari Ormawa/BEM.</p>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-50 flex justify-end gap-3">
                <a 
                    href="{{ route('organizer.events.index') }}" 
                    class="bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 px-5 py-2.5 rounded-lg text-sm font-semibold transition-colors"
                >
                    Batal
                </a>
                <button 
                    type="submit" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition-colors shadow-sm"
                >
                    Perbarui Event
                </button>
            </div>
        </form>
    </div>
</div>
