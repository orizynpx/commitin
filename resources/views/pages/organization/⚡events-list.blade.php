<?php

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Event;

new #[Layout('layouts.app')] class extends Component
{
    public function deleteEvent(string $eventId)
    {
        $event = auth()->user()->events()->findOrFail($eventId);
        
        $myPivot = $event->organizers()->where('users.user_id', auth()->id())->first()?->pivot;
        if (!$myPivot || !in_array($myPivot->organizer_role, ['creator', 'owner'])) {
            session()->flash('error', 'Hanya pembuat (creator) atau pemilik (owner) yang dapat menghapus event ini.');
            return;
        }

        $event->delete();
        session()->flash('success', 'Event berhasil dihapus.');
    }

    public function render()
    {
        $events = auth()->user()->events()
            ->withCount('vacancies')
            ->orderByDesc('created_at')
            ->get();

        return view('pages.organization.⚡events-list', [
            'events' => $events,
        ]);
    }
}; ?>

<div class="max-w-6xl mx-auto py-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-on-surface mb-2">{{ __('Kelola Event Organisasi') }}</h1>
            <p class="text-on-surface-variant">{{ __('Daftar kegiatan organisasi Anda beserta lowongan divisi yang dibuka.') }}</p>
        </div>
        <a 
            href="{{ route('organizer.events.create') }}" 
            class="bg-primary hover:bg-primary-container text-white font-semibold px-5 py-2.5 rounded-lg text-sm transition-colors shadow-sm"
        >
            Buat Event Baru
        </a>
    </div>

    @if(session()->has('success'))
        <div class="bg-secondary-container border border-secondary-container text-on-secondary-container rounded-xl p-4 mb-6 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session()->has('error'))
        <div class="bg-error-container border border-error-container text-on-error-container rounded-xl p-4 mb-6 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-surface-dim overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-surface-container border-b border-surface-dim text-xs font-bold text-on-surface uppercase tracking-wider">
                    <th class="px-6 py-4 whitespace-nowrap">Nama Event</th>
                    <th class="px-6 py-4 whitespace-nowrap">Tanggal Event</th>
                    <th class="px-6 py-4 whitespace-nowrap">Status / Kategori</th>
                    <th class="px-6 py-4 whitespace-nowrap">Jumlah Lowongan</th>
                    <th class="px-6 py-4 text-right whitespace-nowrap">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-dim text-sm">
                @forelse($events as $event)
                    <tr>
                        <td class="px-6 py-4 font-semibold text-on-surface whitespace-nowrap">
                            <a href="{{ route('organizer.events.show', $event->event_id) }}" class="hover:underline text-primary">
                                {{ $event->event_name }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-outline-variant whitespace-nowrap">
                            {{ $event->event_date?->format('d M Y') ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold 
                                {{ $event->is_official ? 'bg-surface-container text-primary border border-primary-fixed-dim' : 'bg-surface-container text-on-surface-variant border border-surface-dim' }}
                            ">
                                {{ $event->is_official ? 'Resmi Kampus' : 'Umum' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 font-medium text-on-surface-variant whitespace-nowrap">
                            {{ $event->vacancies_count }} Divisi
                        </td>
                        <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                            <a 
                                href="{{ route('organizer.events.vacancies.create', $event->event_id) }}" 
                                class="text-xs bg-surface-container text-on-surface-variant border border-surface-dim hover:bg-surface-container px-3 py-1.5 rounded-lg font-semibold"
                            >
                                + Buka Lowongan
                            </a>
                            <a 
                                href="{{ route('organizer.events.edit', $event->event_id) }}" 
                                class="text-xs text-primary hover:underline font-semibold"
                            >
                                Edit
                            </a>
                            <button 
                                wire:click="deleteEvent('{{ $event->event_id }}')" 
                                wire:confirm="Apakah Anda yakin ingin menghapus event ini beserta semua lowongan di dalamnya?"
                                class="text-xs text-error hover:underline font-semibold"
                            >
                                Hapus
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-outline-variant">
                            <svg class="w-12 h-12 text-outline-variant mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span class="block mb-1 font-semibold text-on-surface">Belum Ada Event</span>
                            <span class="text-xs text-outline-variant">Mulailah dengan membuat event pertama untuk membuka lowongan kepanitiaan.</span>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
