<?php

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\VacancyApplication;

new #[Layout('layouts.app')] class extends Component
{
    public function render()
    {
        $applicationsCount = auth()->user()->applications()->count();
        $recentApplications = auth()->user()->applications()
            ->with(['vacancy.event'])
            ->orderByDesc('created_at')
            ->take(3)
            ->get();

        return view('pages.student.⚡dashboard', [
            'applicationsCount' => $applicationsCount,
            'recentApplications' => $recentApplications,
        ]);
    }
}; ?>

<div>
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-[#1e293b] mb-1">Selamat Datang, {{ auth()->user()->name }}!</h2>
        <p class="text-gray-500 text-sm">Temukan lowongan kepanitiaan dan kembangkan pengalaman Anda di lingkungan kampus.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between">
            <div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Cari Lowongan Kepanitiaan</h3>
                <p class="text-gray-500 text-sm mb-4">Temukan divisi kepanitiaan yang sesuai dengan minat dan keahlian utama Anda.</p>
            </div>
            <a href="{{ route('vacancies.index') }}" class="inline-flex items-center text-blue-600 font-semibold hover:text-blue-800 text-sm">
                Mulai Eksplorasi &rarr;
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between">
            <div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Kelola Profil & Portofolio</h3>
                <p class="text-gray-500 text-sm mb-4">Lengkapi data diri, keahlian, dan riwayat pengalaman untuk meningkatkan peluang diterima.</p>
            </div>
            <a href="{{ route('profile') }}" class="inline-flex items-center text-blue-600 font-semibold hover:text-blue-800 text-sm">
                Sunting Profil &rarr;
            </a>
        </div>
    </div>

    @if($applicationsCount > 0)
        <!-- Recent Applications -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold text-gray-900">Status Lamaran Terbaru</h3>
                <a href="{{ route('applications.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-800">
                    Lihat Semua ({{ $applicationsCount }}) &rarr;
                </a>
            </div>

            <div class="divide-y divide-gray-100">
                @foreach($recentApplications as $app)
                    <div class="py-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div>
                            <h4 class="font-bold text-gray-900">{{ $app->vacancy->division }}</h4>
                            <p class="text-xs text-gray-500">{{ $app->vacancy->event->event_name }} &bull; Terdaftar {{ $app->created_at->format('d M Y') }}</p>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase 
                                {{ $app->status === 'accepted' ? 'bg-green-50 text-green-700 border border-green-100' : '' }}
                                {{ $app->status === 'rejected' ? 'bg-red-50 text-red-700 border border-red-100' : '' }}
                                {{ $app->status === 'interviewing' ? 'bg-blue-50 text-blue-700 border border-blue-100' : '' }}
                                {{ $app->status === 'pending' ? 'bg-amber-50 text-amber-700 border border-amber-100' : '' }}
                            ">
                                {{ $app->status }}
                            </span>
                            <a href="{{ route('vacancies.show', $app->vacancy_id) }}" class="text-xs text-blue-600 hover:underline">
                                Detail
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <!-- No Applications Placeholder -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center">
            <div class="max-w-md mx-auto">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                <h4 class="text-lg font-bold text-gray-900 mb-1">Belum Ada Lamaran Aktif</h4>
                <p class="text-gray-500 text-sm mb-6">Anda belum mendaftar ke lowongan kepanitiaan mana pun saat ini.</p>
                <a href="{{ route('vacancies.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition-colors shadow-sm inline-block">
                    Cari Lowongan Sekarang
                </a>
            </div>
        </div>
    @endif
</div>
