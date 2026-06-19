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
        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ __('Lamaran Saya') }}</h1>
        <p class="text-gray-600">{{ __('Pantau status lamaran kepanitiaan Anda di bawah ini.') }}</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    <th class="px-6 py-4">Kepanitiaan & Divisi</th>
                    <th class="px-6 py-4">Tanggal Daftar</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm">
                @forelse($applications as $app)
                    <tr>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-gray-900">{{ $app->vacancy->division }}</div>
                            <div class="text-xs text-gray-500">{{ $app->vacancy->event->event_name }}</div>
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
                        <td class="px-6 py-4">
                            <a href="{{ route('vacancies.show', $app->vacancy_id) }}" class="text-blue-600 hover:text-blue-800 font-semibold text-xs">
                                Lihat Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                            <span class="block mb-1 font-semibold text-gray-900">Belum Ada Lamaran</span>
                            <span class="text-xs text-gray-400">Silakan jelajahi lowongan yang tersedia di menu Eksplorasi.</span>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
