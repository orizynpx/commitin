<?php

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\VacancyApplication;

new #[Layout('layouts.app')] class extends Component
{
    public function render()
    {
        $applications = auth()->user()->applications()
            ->with(['vacancy.event'])
            ->orderByDesc('created_at')
            ->get();

        return view('pages.student.⚡applications', [
            'applications' => $applications,
        ]);
    }
}; ?>

<div class="max-w-4xl mx-auto py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-on-surface mb-2">{{ __('Lamaran Saya') }}</h1>
        <p class="text-on-surface-variant">{{ __('Pantau status lamaran kepanitiaan Anda di bawah ini.') }}</p>
    </div>

    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-surface-dim overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-surface-container border-b border-surface-dim text-xs font-bold text-on-surface uppercase tracking-wider">
                    <th class="px-6 py-4 whitespace-nowrap">Kepanitiaan & Divisi</th>
                    <th class="px-6 py-4 whitespace-nowrap">Tanggal Daftar</th>
                    <th class="px-6 py-4 whitespace-nowrap">Status</th>
                    <th class="px-6 py-4 whitespace-nowrap">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-dim text-sm">
                @forelse($applications as $app)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-semibold text-on-surface">{{ $app->vacancy->division }}</div>
                            <div class="text-xs text-outline-variant">{{ $app->vacancy->event->event_name }}</div>
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
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="{{ route('vacancies.show', $app->vacancy_id) }}" class="text-primary hover:text-on-primary-container font-semibold text-xs">
                                Lihat Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-outline-variant">
                            <svg class="w-12 h-12 text-outline-variant mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                            <span class="block mb-1 font-semibold text-on-surface">Belum Ada Lamaran</span>
                            <span class="text-xs text-outline-variant">Silakan jelajahi lowongan yang tersedia di menu Eksplorasi.</span>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
