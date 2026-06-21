<?php

use App\Models\Event;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component
{
    public Event $event;

    public function mount(Event $event): void
    {
        $isCollaborator = $event->organizers()->where('users.user_id', auth()->id())->exists();
        if (!$isCollaborator) {
            abort(403, 'Anda tidak memiliki akses ke event ini.');
        }

        $this->event = $event->load(['organizers', 'vacancies.skills'])->loadCount('vacancies');
    }
}; ?>

@section('title', 'Detail Event')

<div class="space-y-8 py-6">
    <div class="mb-6">
        <a href="{{ route('organizer.events.index') }}" class="text-primary hover:text-on-primary-container text-sm font-semibold flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg> Kembali ke Daftar Event
        </a>
    </div>

    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-surface-dim p-6">
        <div class="flex justify-between items-start mb-4">
            <div>
                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase 
                    {{ $event->is_official ? 'bg-surface-container text-primary border border-primary-fixed-dim' : 'bg-surface-container text-on-surface-variant border border-surface-dim' }}
                ">
                    {{ $event->is_official ? 'Resmi Kampus' : 'Umum' }}
                </span>
                <h1 class="text-3xl font-bold text-on-surface mt-2">{{ $event->event_name }}</h1>
                <p class="text-xs text-outline-variant mt-1">Tanggal: {{ $event->event_date?->format('d M Y') ?? '-' }}</p>
            </div>
            
            @php
                $myPivot = $event->organizers()->where('users.user_id', auth()->id())->first()?->pivot;
                $canEdit = $myPivot && in_array($myPivot->organizer_role, ['creator', 'owner']);
            @endphp
            @if($canEdit)
                <div class="flex gap-2">
                    <a href="{{ route('organizer.events.edit', $event->event_id) }}" class="text-xs bg-primary text-white hover:bg-primary-container px-3 py-1.5 rounded-lg font-semibold shadow-sm">
                        Edit Event
                    </a>
                    <a href="{{ route('organizer.events.vacancies.create', $event->event_id) }}" class="text-xs bg-surface-container text-on-surface-variant border border-surface-dim hover:bg-surface-container px-3 py-1.5 rounded-lg font-semibold">
                        + Tambah Lowongan
                    </a>
                    <a href="{{ route('organizer.events.team', $event->event_id) }}" class="text-xs bg-surface-container text-on-surface-variant border border-surface-dim hover:bg-surface-container px-3 py-1.5 rounded-lg font-semibold">
                        Kelola Tim
                    </a>
                </div>
            @endif
        </div>

        <p class="text-sm text-on-surface-variant whitespace-pre-line leading-relaxed">{{ $event->description ?? 'Tidak ada deskripsi.' }}</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-surface-dim p-6">
                <h3 class="text-lg font-bold text-on-surface mb-4">Daftar Lowongan Divisi</h3>
                
                @php
                    $vacancies = $event->vacancies()->withCount('applications')->get();
                @endphp
                
                @if($vacancies->isEmpty())
                    <p class="text-sm text-outline-variant italic">Belum ada lowongan divisi yang dibuka untuk event ini.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="bg-surface-container border-b border-surface-dim text-xs font-bold text-on-surface uppercase tracking-wider">
                                    <th class="px-6 py-4">Nama Divisi</th>
                                    <th class="px-6 py-4">Keahlian Disyaratkan</th>
                                    <th class="px-6 py-4">Status</th>
                                    <th class="px-6 py-4 text-right">Aksi / Pelamar</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-surface-dim">
                                @foreach($vacancies as $vac)
                                    <tr class="hover:bg-surface-container-low transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-on-surface">{{ $vac->division }}</div>
                                            <div class="text-xs text-outline-variant line-clamp-1 mt-0.5" title="{{ $vac->vacancy_description }}">{{ $vac->vacancy_description }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex flex-wrap gap-1">
                                                @forelse($vac->skills as $s)
                                                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-surface-container text-on-surface-variant border border-surface-dim">
                                                        {{ $s->skill_name }}
                                                    </span>
                                                @empty
                                                    <span class="text-xs text-outline-variant italic">Umum</span>
                                                @endforelse
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold uppercase 
                                                {{ $vac->status === 'OPEN' ? 'bg-secondary-container text-on-secondary-container' : 'bg-surface-container text-outline-variant' }}
                                            ">
                                                {{ $vac->status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right whitespace-nowrap space-x-2">
                                            @if($myPivot && in_array($myPivot->organizer_role, ['creator', 'owner', 'manager']))
                                                <a href="{{ route('organizer.vacancies.applications', $vac->vacancy_id) }}" class="text-xs text-primary hover:underline font-semibold">
                                                    Lihat Pelamar ({{ $vac->applications_count }})
                                                </a>
                                            @endif
                                            @if($canEdit)
                                                <a href="{{ route('organizer.vacancies.edit', $vac->vacancy_id) }}" class="text-xs text-outline-variant hover:text-primary font-semibold">
                                                    Edit
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-surface-dim p-6">
                <h3 class="text-lg font-bold text-on-surface mb-4">Tim Penyelenggara</h3>
                <div class="space-y-3">
                    @foreach($event->organizers as $org)
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center font-bold text-sm shrink-0">
                                {{ substr($org->name, 0, 1) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-on-surface truncate">{{ $org->name }}</p>
                                <p class="text-[10px] font-bold text-outline-variant uppercase tracking-wider mt-0.5">
                                    {{ $org->pivot->organizer_role === 'creator' ? 'Pembuat' : ($org->pivot->organizer_role === 'owner' ? 'Pemilik' : ($org->pivot->organizer_role === 'manager' ? 'Manajer' : $org->pivot->organizer_role)) }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
