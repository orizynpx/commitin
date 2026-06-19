<?php

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Vacancy;
use App\Models\VacancyApplication;

new #[Layout('layouts.app')] class extends Component
{
    public Vacancy $vacancy;

    public function mount(string $vacancy): void
    {
        $this->vacancy = Vacancy::whereHas('event.organizers', function($q) {
            $q->where('event_organizers.user_id', auth()->id());
        })->with('event')->findOrFail($vacancy);
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
        <a href="{{ route('organization.dashboard') }}" class="text-blue-600 hover:text-blue-800 text-sm font-semibold flex items-center gap-1">
            &larr; Kembali ke Dashboard
        </a>
    </div>

    <div class="mb-8">
        <span class="text-xs font-semibold uppercase tracking-wider text-blue-600 mb-1 block">
            Event: {{ $vacancy->event->event_name }}
        </span>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Pelamar Divisi: {{ $vacancy->division }}</h1>
        <p class="text-gray-600">{{ __('Daftar pelamar khusus untuk lowongan kepanitiaan ini.') }}</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    <th class="px-6 py-4">Nama Pelamar</th>
                    <th class="px-6 py-4">Tanggal Daftar</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm">
                @forelse($applications as $app)
                    <tr>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-gray-900">{{ $app->user->name }}</div>
                            <div class="text-xs text-gray-500">{{ $app->user->email }}</div>
                        </td>
                        <td class="px-6 py-4 text-gray-500">
                            {{ $app->created_at->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase 
                                {{ $app->status === 'accepted' ? 'bg-green-50 text-green-700 border border-green-100' : '' }}
                                {{ $app->status === 'rejected' ? 'bg-red-50 text-red-700 border border-red-100' : '' }}
                                {{ $app->status === 'interviewing' ? 'bg-blue-50 text-blue-700 border border-blue-100' : '' }}
                                {{ $app->status === 'pending' ? 'bg-amber-50 text-amber-700 border border-amber-100' : '' }}
                            ">
                                {{ $app->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a 
                                href="{{ route('organization.applications.show', $app->vacancy_application_id) }}" 
                                class="text-xs bg-blue-600 hover:bg-blue-700 text-white font-semibold px-3 py-1.5 rounded-lg shadow-sm"
                            >
                                Kelola Lamaran
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            <span class="block mb-1 font-semibold text-gray-900">Belum Ada Pelamar</span>
                            <span class="text-xs text-gray-400">Pendaftaran divisi ini belum memiliki pelamar masuk saat ini.</span>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
