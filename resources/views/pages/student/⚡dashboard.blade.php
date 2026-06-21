<?php

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Vacancy;
use App\Models\VacancyApplication;
use Illuminate\Support\Facades\DB;

new #[Layout('layouts.app')] class extends Component
{
    public function render()
    {
        $user = auth()->user();
        $profile = $user->studentProfile ? clone $user->studentProfile : null;
        $userSkills = clone $user->skills;
        
        $applications = $user->applications()->with(['vacancy.event'])->orderByDesc('created_at')->get();
        
        $totalApplications = $applications->count();
        $appPending = $applications->where('status', 'pending')->count();
        $appInterviewing = $applications->where('status', 'interviewing')->count();
        $appAccepted = $applications->where('status', 'accepted')->count();
        $appRejected = $applications->where('status', 'rejected')->count();

        $upcomingInterviews = $applications->filter(function($app) {
            return $app->status === 'interviewing' && 
                   $app->interview_scheduled_at && 
                   \Carbon\Carbon::parse($app->interview_scheduled_at)->isFuture();
        })->sortBy('interview_scheduled_at')->take(5);

        $rejectedLogs = $applications->filter(function($app) {
            return $app->status === 'rejected' && !empty($app->feedback);
        })->take(5);

        $userSkillIds = $userSkills->pluck('skill_id')->toArray();
        $recommendedVacancies = collect();

        if(!empty($userSkillIds)) {
            $recommendedVacancies = Vacancy::with(['event', 'skills'])
                ->where('status', 'OPEN')
                ->whereHas('skills', function($q) use ($userSkillIds) {
                    $q->whereIn('skills.skill_id', $userSkillIds);
                })
                ->get()
                ->map(function ($vacancy) use ($userSkillIds) {
                    $reqSkills = $vacancy->skills->pluck('skill_id')->toArray();
                    $matched = count(array_intersect($userSkillIds, $reqSkills));
                    $vacancy->match_count = $matched;
                    return $vacancy;
                })
                ->sortByDesc('match_count')
                ->take(5);
        }

        return view('pages.student.⚡dashboard', [
            'user' => $user,
            'profile' => $profile,
            'userSkills' => $userSkills,
            'applications' => $applications,
            'totalApplications' => $totalApplications,
            'appPending' => $appPending,
            'appInterviewing' => $appInterviewing,
            'appAccepted' => $appAccepted,
            'appRejected' => $appRejected,
            'upcomingInterviews' => $upcomingInterviews,
            'rejectedLogs' => $rejectedLogs,
            'recommendedVacancies' => $recommendedVacancies,
            'userSkillIds' => $userSkillIds,
        ]);
    }
}; ?>

<div class="space-y-8 py-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-on-surface tracking-tight">{{ __('Dasbor Pelamar') }}</h1>
            <p class="text-on-surface-variant text-sm mt-1">{{ __('Pantau status lamaran, jadwal wawancara, dan temukan rekomendasi lowongan.') }}</p>
        </div>
    </div>

    <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-surface-dim p-6">
        @if($user->blocked_at)
            <div class="mb-6 bg-error-container border border-error text-error rounded-xl p-4 shadow-sm flex items-start gap-3">
                <svg class="w-6 h-6 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <div>
                    <h3 class="font-bold text-sm uppercase tracking-wider mb-1">Peringatan: Akun Diblokir</h3>
                    <p class="text-xs">Akun Anda telah dinonaktifkan oleh Administrator. Anda tidak dapat mengajukan lamaran baru.<br><strong>Alasan:</strong> {{ $user->block_reason }}</p>
                </div>
            </div>
        @endif

        <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
            @if($user->avatar_url)
                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-20 h-20 rounded-full object-cover shrink-0 shadow-sm shadow-primary-fixed-dim">
            @else
                <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=2563eb&color=fff" alt="{{ $user->name }}" class="w-20 h-20 rounded-full shrink-0 shadow-sm shadow-primary-fixed-dim">
            @endif
            <div class="flex-1">
                <h2 class="text-2xl font-bold text-on-surface mb-1">{{ $user->name }}</h2>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-on-surface-variant mb-3">
                    <span class="flex items-center gap-1"><svg class="w-4 h-4 text-outline-variant" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg> {{ $profile?->student_id ?? '-' }}</span>
                    <span class="flex items-center gap-1"><svg class="w-4 h-4 text-outline-variant" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg> {{ $profile?->faculty ?? '-' }}</span>
                    <span class="flex items-center gap-1"><svg class="w-4 h-4 text-outline-variant" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg> {{ $profile?->study_program ?? '-' }}</span>
                </div>
                <div class="flex flex-wrap gap-1.5">
                    @forelse($userSkills as $skill)
                        <span class="px-2 py-0.5 bg-surface-container border border-surface-dim rounded text-[10px] font-bold text-on-surface uppercase tracking-wider">{{ $skill->skill_name }}</span>
                    @empty
                        <span class="text-xs italic text-outline-variant">Belum ada tag keahlian ditambahkan.</span>
                    @endforelse
                </div>
            </div>
            <a href="{{ route('profile') }}" class="bg-surface-container hover:bg-surface-dim text-on-surface-variant text-xs font-bold px-4 py-2 rounded-lg transition-colors border border-surface-dim">Edit Profil</a>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-surface-dim p-4">
            <span class="text-[10px] font-bold text-outline-variant uppercase mb-1 block">Total Diajukan</span>
            <strong class="text-2xl font-black text-on-surface">{{ $totalApplications }}</strong>
        </div>
        <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-surface-dim p-4">
            <span class="text-[10px] font-bold text-on-secondary-container uppercase mb-1 block">Pending</span>
            <strong class="text-2xl font-black text-on-surface">{{ $appPending }}</strong>
        </div>
        <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-surface-dim p-4">
            <span class="text-[10px] font-bold text-primary uppercase mb-1 block">Wawancara</span>
            <strong class="text-2xl font-black text-on-surface">{{ $appInterviewing }}</strong>
        </div>
        <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-surface-dim p-4">
            <span class="text-[10px] font-bold text-on-secondary-container uppercase mb-1 block text-emerald-600">Diterima</span>
            <strong class="text-2xl font-black text-on-surface">{{ $appAccepted }}</strong>
        </div>
        <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-surface-dim p-4">
            <span class="text-[10px] font-bold text-error uppercase mb-1 block">Ditolak</span>
            <strong class="text-2xl font-black text-on-surface">{{ $appRejected }}</strong>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 flex flex-col gap-6">
            <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-surface-dim overflow-hidden">
                <div class="p-6 border-b border-surface-dim flex justify-between items-center">
                    <h3 class="text-lg font-bold text-on-surface flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                        Status Lamaran Anda
                    </h3>
                    <a href="{{ route('applications.index') }}" class="text-xs font-semibold text-primary hover:underline">Lihat Semua</a>
                </div>
                @if($applications->isEmpty())
                    <div class="p-8 text-center text-outline-variant italic text-sm">Anda belum mengajukan lamaran ke kepanitiaan manapun.</div>
                @else
                    <div class="divide-y divide-surface-dim">
                        @foreach($applications->take(5) as $app)
                            <div class="p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 hover:bg-surface-container-low transition-colors">
                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-primary mb-1 block">{{ $app->vacancy->event->event_name }}</span>
                                    <h4 class="font-bold text-on-surface">{{ $app->vacancy->division }}</h4>
                                    <p class="text-xs text-outline-variant mt-1">Diajukan: {{ $app->created_at->format('d M Y') }}</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border 
                                        {{ $app->status === 'accepted' ? 'bg-secondary-container text-emerald-800 border-emerald-200' : '' }}
                                        {{ $app->status === 'rejected' ? 'bg-error-container text-error border-error-container' : '' }}
                                        {{ $app->status === 'interviewing' ? 'bg-surface-container text-primary border-primary-fixed-dim' : '' }}
                                        {{ $app->status === 'pending' ? 'bg-surface-container text-outline-variant border-surface-dim' : '' }}
                                    ">
                                        {{ $app->status === 'accepted' ? 'Diterima' : ($app->status === 'rejected' ? 'Ditolak' : ($app->status === 'interviewing' ? 'Wawancara' : 'Menunggu')) }}
                                    </span>
                                    <a href="{{ route('vacancies.show', $app->vacancy_id) }}" class="text-xs font-semibold text-on-surface-variant hover:text-primary transition-colors flex items-center gap-1">Detail <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg></a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-surface-dim overflow-hidden">
                <div class="p-6 border-b border-surface-dim">
                    <h3 class="text-lg font-bold text-on-surface flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        Rekomendasi Cerdas (Smart Match)
                    </h3>
                    <p class="text-xs text-outline-variant mt-1">Lowongan kepanitiaan yang paling cocok dengan keahlian Anda.</p>
                </div>
                @if($recommendedVacancies->isEmpty())
                    <div class="p-8 text-center text-outline-variant italic text-sm">Silakan lengkapi tag keahlian di profil Anda agar kami dapat memberikan rekomendasi lowongan yang sesuai.</div>
                @else
                    <div class="divide-y divide-surface-dim">
                        @foreach($recommendedVacancies as $vac)
                            <div class="p-4 hover:bg-surface-container-low transition-colors">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-outline-variant">{{ $vac->event->event_name }}</span>
                                            @if($vac->event->is_official)
                                                <span class="text-[10px] bg-primary text-white px-1.5 py-0.5 rounded flex items-center gap-0.5" title="Event Resmi Kampus"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg> Official</span>
                                            @endif
                                        </div>
                                        <h4 class="font-bold text-on-surface">{{ $vac->division }}</h4>
                                    </div>
                                    <a href="{{ route('vacancies.show', $vac->vacancy_id) }}" class="text-xs font-semibold bg-surface-container border border-surface-dim px-3 py-1.5 rounded-lg hover:bg-surface-dim transition-colors">Lihat</a>
                                </div>
                                <div class="flex flex-wrap gap-1 mt-2">
                                    @foreach($vac->skills as $reqSkill)
                                        <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase {{ in_array($reqSkill->skill_id, $userSkillIds) ? 'bg-secondary-container text-on-secondary-container' : 'bg-surface-container text-outline-variant' }}">{{ $reqSkill->skill_name }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="flex flex-col gap-6">
            <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-primary-fixed-dim overflow-hidden">
                <div class="p-6 border-b border-surface-dim">
                    <h3 class="text-lg font-bold text-on-surface flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Jadwal Wawancara
                    </h3>
                </div>
                @if($upcomingInterviews->isEmpty())
                    <div class="p-8 text-center text-outline-variant italic text-sm">Anda belum memiliki jadwal wawancara terdekat.</div>
                @else
                    <div class="p-4 space-y-4">
                        @foreach($upcomingInterviews as $ivApp)
                            <div class="bg-surface-container-lowest border border-surface-dim rounded-xl p-4 shadow-sm relative overflow-hidden">
                                <div class="absolute top-0 left-0 w-1 h-full bg-primary"></div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-outline-variant mb-1 block">{{ $ivApp->vacancy->event->event_name }}</span>
                                <h4 class="font-bold text-on-surface mb-2">{{ $ivApp->vacancy->division }}</h4>
                                
                                <div class="space-y-1.5 mt-3">
                                    <div class="flex items-center gap-2 text-xs text-on-surface-variant">
                                        <svg class="w-3.5 h-3.5 text-primary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <span class="font-semibold">{{ \Carbon\Carbon::parse($ivApp->interview_scheduled_at)->format('d M Y - H:i') }} WIB</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs text-on-surface-variant">
                                        <svg class="w-3.5 h-3.5 text-primary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                                        <span class="uppercase font-semibold text-[10px] bg-surface-container px-1.5 py-0.5 rounded">{{ $ivApp->interview_format ?? 'TBA' }}</span>
                                    </div>
                                    @if($ivApp->interview_location)
                                        <div class="flex items-start gap-2 text-xs text-on-surface-variant mt-2 pt-2 border-t border-surface-dim">
                                            <svg class="w-3.5 h-3.5 text-primary shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                            <a href="{{ filter_var($ivApp->interview_location, FILTER_VALIDATE_URL) ? $ivApp->interview_location : '#' }}" target="_blank" class="{{ filter_var($ivApp->interview_location, FILTER_VALIDATE_URL) ? 'text-primary hover:underline' : '' }} break-words">{{ $ivApp->interview_location }}</a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-error-container overflow-hidden">
                <div class="p-6 border-b border-surface-dim">
                    <h3 class="text-lg font-bold text-on-surface flex items-center gap-2">
                        <svg class="w-5 h-5 text-error" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Log Evaluasi (Penolakan)
                    </h3>
                </div>
                @if($rejectedLogs->isEmpty())
                    <div class="p-8 text-center text-outline-variant italic text-sm">Tidak ada catatan evaluasi dari panitia sejauh ini.</div>
                @else
                    <div class="divide-y divide-surface-dim">
                        @foreach($rejectedLogs as $log)
                            <div class="p-4 bg-error-container/20">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-error mb-1 block">{{ $log->vacancy->event->event_name }} &bull; {{ $log->vacancy->division }}</span>
                                <div class="text-sm text-on-surface-variant italic border-l-2 border-error pl-3 my-2">"{{ $log->feedback }}"</div>
                                <span class="text-[10px] text-outline-variant">Tercatat: {{ $log->updated_at->format('d M Y') }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
