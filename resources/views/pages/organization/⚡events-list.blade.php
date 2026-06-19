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
            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ __('Kelola Event Organisasi') }}</h1>
            <p class="text-gray-600">{{ __('Daftar kegiatan organisasi Anda beserta lowongan divisi yang dibuka.') }}</p>
        </div>
        <a 
            href="{{ route('organizer.events.create') }}" 
            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2.5 rounded-lg text-sm transition-colors shadow-sm"
        >
            Buat Event Baru
        </a>
    </div>

    @if(session()->has('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl p-4 mb-6 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session()->has('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl p-4 mb-6 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    <th class="px-6 py-4">Nama Event</th>
                    <th class="px-6 py-4">Tanggal Event</th>
                    <th class="px-6 py-4">Status / Kategori</th>
                    <th class="px-6 py-4">Jumlah Lowongan</th>
                    <th class="px-6 py-4 text-right">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm">
                @forelse($events as $event)
                    <tr>
                        <td class="px-6 py-4 font-semibold text-gray-900">
                            {{ $event->event_name }}
                        </td>
                        <td class="px-6 py-4 text-gray-500">
                            {{ $event->event_date?->format('d M Y') ?? '-' }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold 
                                {{ $event->is_official ? 'bg-indigo-50 text-indigo-700 border border-indigo-100' : 'bg-gray-50 text-gray-700 border border-gray-100' }}
                            ">
                                {{ $event->is_official ? 'Resmi Kampus' : 'Umum' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-700">
                            {{ $event->vacancies_count }} Divisi
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a 
                                href="{{ route('organizer.events.vacancies.create', $event->event_id) }}" 
                                class="text-xs bg-gray-50 text-gray-700 border border-gray-200 hover:bg-gray-100 px-3 py-1.5 rounded-lg font-semibold"
                            >
                                + Buka Lowongan
                            </a>
                            <a 
                                href="{{ route('organizer.events.edit', $event->event_id) }}" 
                                class="text-xs text-blue-600 hover:underline font-semibold"
                            >
                                Edit
                            </a>
                            <button 
                                wire:click="deleteEvent('{{ $event->event_id }}')" 
                                wire:confirm="Apakah Anda yakin ingin menghapus event ini beserta semua lowongan di dalamnya?"
                                class="text-xs text-red-600 hover:underline font-semibold"
                            >
                                Hapus
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span class="block mb-1 font-semibold text-gray-900">Belum Ada Event</span>
                            <span class="text-xs text-gray-400">Mulailah dengan membuat event pertama untuk membuka lowongan kepanitiaan.</span>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
