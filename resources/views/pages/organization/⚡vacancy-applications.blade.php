<?php

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Vacancy;
use App\Models\VacancyApplication;

new #[Layout('layouts.app')] class extends Component
{
    public Vacancy $vacancy;

    public function mount(Vacancy $vacancy): void
    {
        $hasAccess = $vacancy->event->organizers()->where('users.user_id', auth()->id())->exists();
        if (!$hasAccess) {
            abort(403, 'Unauthorized.');
        }

        $this->vacancy = $vacancy->loadMissing('event');
    }

    public function render()
    {
        $applications = $this->vacancy->applications()
            ->with('user')
            ->orderByDesc('created_at')
            ->get();

        return view('pages.organization.⚡vacancy-applications', [
            'applications' => $applications,
        ]);
    }
}; ?>

<div class="max-w-6xl mx-auto py-8">
    <div class="mb-6">
        <a href="{{ route('organizer.dashboard') }}" class="text-primary hover:text-on-primary-container text-sm font-semibold flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg> Kembali ke Dashboard
        </a>
    </div>

    <div class="mb-8">
        <span class="text-xs font-semibold uppercase tracking-wider text-primary mb-1 block">
            Event: {{ $vacancy->event->event_name }}
        </span>
        <h1 class="text-3xl font-bold text-on-surface mb-2">Pelamar Divisi: {{ $vacancy->division }}</h1>
        <p class="text-on-surface-variant">{{ __('Daftar pelamar khusus untuk lowongan kepanitiaan ini.') }}</p>
    </div>

    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-surface-dim overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-surface-container border-b border-surface-dim text-xs font-bold text-on-surface uppercase tracking-wider">
                    <th class="px-6 py-4 whitespace-nowrap">Nama Pelamar</th>
                    <th class="px-6 py-4 whitespace-nowrap">Tanggal Daftar</th>
                    <th class="px-6 py-4 whitespace-nowrap">Status</th>
                    <th class="px-6 py-4 text-right whitespace-nowrap">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-dim text-sm">
                @forelse($applications as $app)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-semibold text-on-surface">{{ $app->user->name }}</div>
                            <div class="text-xs text-outline-variant">{{ $app->user->email }}</div>
                        </td>
                        <td class="px-6 py-4 text-outline-variant whitespace-nowrap">
                            {{ $app->created_at->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase 
                                {{ $app->status === 'accepted' ? 'bg-secondary-container text-on-secondary-container border border-secondary-container' : '' }}
                                {{ $app->status === 'rejected' ? 'bg-error-container text-error border border-error-container' : '' }}
                                {{ $app->status === 'interviewing' ? 'bg-surface-container text-primary border border-primary-fixed-dim' : '' }}
                                {{ $app->status === 'pending' ? 'bg-secondary-container text-on-secondary-container border border-secondary-container' : '' }}
                            ">
                                {{ $app->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <a 
                                href="{{ route('organizer.applications.show', $app->vacancy_application_id) }}" 
                                class="text-xs bg-primary hover:bg-primary-container text-white font-semibold px-3 py-1.5 rounded-lg shadow-sm"
                            >
                                Kelola Lamaran
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-outline-variant">
                            <svg class="w-12 h-12 text-outline-variant mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            <span class="block mb-1 font-semibold text-on-surface">Belum Ada Pelamar</span>
                            <span class="text-xs text-outline-variant">Pendaftaran divisi ini belum memiliki pelamar masuk saat ini.</span>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
