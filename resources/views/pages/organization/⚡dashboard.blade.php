<?php

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Event;
use App\Models\Vacancy;
use App\Models\VacancyApplication;
use Illuminate\Support\Facades\DB;

new #[Layout('layouts.app')] class extends Component
{
    public function render()
    {
        $orgId = auth()->id();
        $user = auth()->user();
        $profile = clone $user->organizationProfile;

        // 1. My Events Pipeline & High-level Metrics
        $myEvents = Event::whereHas('organizers', function($q) use ($orgId) {
            $q->where('event_organizers.user_id', $orgId);
        })->with(['organizers', 'vacancies.applications.user'])->get();

        $totalEvents = $myEvents->count();
        $totalOpenVacancies = 0;
        $totalApplications = 0;
        $appPending = 0;
        $appInterviewing = 0;
        $appAccepted = 0;
        $appRejected = 0;

        $eventList = [];
        $collaborators = [];
        $upcomingInterviews = [];

        foreach($myEvents as $event) {
            $myRole = $event->organizers->firstWhere('user_id', $orgId)->pivot->role ?? 'MEMBER';
            $eventVacancies = $event->vacancies->where('status', 'OPEN')->count();
            $totalOpenVacancies += $eventVacancies;

            $eventApplicants = 0;
            foreach($event->vacancies as $vac) {
                $eventApplicants += $vac->applications->count();
                foreach($vac->applications as $app) {
                    $totalApplications++;
                    if($app->status === 'pending') $appPending++;
                    if($app->status === 'interviewing') {
                        $appInterviewing++;
                        if($app->interview_scheduled_at && \Carbon\Carbon::parse($app->interview_scheduled_at)->isFuture()) {
                            $upcomingInterviews[] = [
                                'candidate' => $app->user->name ?? 'Kandidat',
                                'division' => $vac->division,
                                'time' => $app->interview_scheduled_at,
                                'format' => $app->interview_format,
                                'location' => $app->interview_location,
                            ];
                        }
                    }
                    if($app->status === 'accepted') $appAccepted++;
                    if($app->status === 'rejected') $appRejected++;
                }
            }

            $eventList[] = [
                'id' => $event->event_id,
                'name' => $event->event_name,
                'date' => $event->event_date,
                'is_past' => \Carbon\Carbon::parse($event->event_date)->isPast(),
                'role' => $myRole,
                'active_vacancies' => $eventVacancies,
                'applicants' => $eventApplicants,
            ];

            foreach($event->organizers as $org) {
                if($org->user_id !== $orgId) {
                    $collaborators[$org->user_id] = [
                        'name' => $org->name,
                        'email' => $org->email,
                        'role' => $org->pivot->role,
                        'event' => $event->event_name
                    ];
                }
            }
        }

        // Sort upcoming interviews by date
        usort($upcomingInterviews, function($a, $b) {
            return strtotime($a['time']) - strtotime($b['time']);
        });

        // Limit upcoming interviews to show only top 5
        $upcomingInterviews = array_slice($upcomingInterviews, 0, 5);

        return view('pages.organization.⚡dashboard', [
            'user' => $user,
            'profile' => $profile,
            'totalEvents' => $totalEvents,
            'totalOpenVacancies' => $totalOpenVacancies,
            'totalApplications' => $totalApplications,
            'appPending' => $appPending,
            'appInterviewing' => $appInterviewing,
            'appAccepted' => $appAccepted,
            'appRejected' => $appRejected,
            'eventList' => $eventList,
            'collaborators' => array_values($collaborators),
            'upcomingInterviews' => $upcomingInterviews,
        ]);
    }
}; ?>

<div class="space-y-8 py-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-on-surface tracking-tight">{{ __('Dasbor Penyelenggara') }}</h1>
            <p class="text-on-surface-variant text-sm mt-1">{{ __('Pantau kegiatan, manajemen kolaborator, dan status perekrutan panitia Anda.') }}</p>
        </div>
    </div>

    @php
        $status = $profile->verification_status ?? 'pending';
    @endphp

    @if ($status === 'pending')
        <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-surface-dim p-8 text-center my-8">
            <div class="w-16 h-16 bg-secondary-container text-on-secondary-container rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <h2 class="text-xl font-bold text-on-surface mb-2">Akun Sedang Ditinjau Admin</h2>
            <p class="text-on-surface-variant text-sm mb-6 max-w-lg mx-auto">Anda belum dapat membuat kegiatan baru sebelum admin memverifikasi profil organisasi Anda.</p>
        </div>
    @elseif ($status === 'rejected')
        <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-error-container p-8 text-center my-8">
            <div class="w-16 h-16 bg-error-container text-error rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <h2 class="text-xl font-bold text-error mb-2">Verifikasi Ditolak</h2>
            <p class="text-on-surface-variant text-sm mb-6 max-w-lg mx-auto">Harap perbarui profil Anda dengan informasi yang valid, atau hubungi administrator.</p>
        </div>
    @else
        <!-- Organization Profile & Trust Snapshot -->
        <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-surface-dim p-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-full bg-primary-container text-on-primary-container flex items-center justify-center text-2xl font-bold shrink-0">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <div>
                    <h2 class="text-xl font-bold text-on-surface flex items-center gap-2">
                        {{ $user->name }}
                        <svg class="w-5 h-5 text-primary" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                    </h2>
                    <p class="text-sm text-on-surface-variant mt-1">Tingkat: <span class="font-semibold uppercase">{{ str_replace('_', ' ', $profile->organization_level) }}</span></p>
                </div>
            </div>
            <div class="text-left md:text-right">
                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase bg-surface-container text-primary border border-primary-fixed-dim mb-1">Terverifikasi</span>
                <p class="text-xs text-outline-variant">Sejak {{ $profile->verified_at ? \Carbon\Carbon::parse($profile->verified_at)->format('d M Y') : 'N/A' }}</p>
            </div>
        </div>

        <!-- High-Level Operational Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-surface-dim p-6">
                <span class="text-xs font-bold text-outline-variant uppercase mb-2 block">Total Kegiatan</span>
                <strong class="text-3xl font-black text-on-surface">{{ $totalEvents }}</strong>
            </div>
            <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-surface-dim p-6">
                <span class="text-xs font-bold text-outline-variant uppercase mb-2 block">Lowongan Aktif</span>
                <strong class="text-3xl font-black text-on-surface">{{ $totalOpenVacancies }}</strong>
            </div>
            <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-surface-dim p-6">
                <span class="text-xs font-bold text-outline-variant uppercase mb-2 block">Total Pelamar</span>
                <strong class="text-3xl font-black text-on-surface">{{ $totalApplications }}</strong>
            </div>
        </div>

        <!-- Recruitment Funnel Widget -->
        <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-surface-dim p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-on-surface flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    Funnel Pelamar Aktif
                </h3>
                @if($appPending > 0)
                    <span class="bg-error text-white text-xs font-bold px-3 py-1 rounded-full animate-pulse shadow-sm">
                        {{ $appPending }} Pending Action Required!
                    </span>
                @endif
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-surface-container p-4 rounded-xl border border-surface-dim text-center relative overflow-hidden">
                    <span class="text-xs font-semibold text-outline-variant uppercase mb-1 block">Tinjauan Awal (Pending)</span>
                    <strong class="text-2xl text-on-surface">{{ $appPending }}</strong>
                </div>
                <div class="bg-surface-container p-4 rounded-xl border border-surface-dim text-center relative overflow-hidden">
                    <span class="text-xs font-semibold text-primary uppercase mb-1 block">Wawancara</span>
                    <strong class="text-2xl text-on-surface">{{ $appInterviewing }}</strong>
                </div>
                <div class="bg-surface-container p-4 rounded-xl border border-surface-dim text-center relative overflow-hidden">
                    <span class="text-xs font-semibold text-on-secondary-container uppercase mb-1 block">Diterima</span>
                    <strong class="text-2xl text-on-surface">{{ $appAccepted }}</strong>
                </div>
                <div class="bg-surface-container p-4 rounded-xl border border-surface-dim text-center relative overflow-hidden">
                    <span class="text-xs font-semibold text-error uppercase mb-1 block">Ditolak</span>
                    <strong class="text-2xl text-on-surface">{{ $appRejected }}</strong>
                </div>
            </div>
        </div>

        <!-- Middle Section: Pipeline & Interviews -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- My Events Pipeline -->
            <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-surface-dim flex flex-col h-full overflow-hidden">
                <div class="p-6 border-b border-surface-dim">
                    <h3 class="text-lg font-bold text-on-surface flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        Kegiatan Saya (Pipeline)
                    </h3>
                </div>
                <div class="flex-1 overflow-auto">
                    @if(count($eventList) === 0)
                        <div class="p-8 text-center text-outline-variant italic text-sm">Belum ada kegiatan yang dikelola.</div>
                    @else
                        <div class="divide-y divide-surface-dim">
                            @foreach($eventList as $ev)
                                <a href="{{ route('organizer.events.edit', $ev['id']) }}" class="block p-4 hover:bg-surface-container-low transition-colors group">
                                    <div class="flex justify-between items-start mb-2">
                                        <h4 class="font-bold text-on-surface group-hover:text-primary transition-colors">{{ $ev['name'] }}</h4>
                                        <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full border {{ $ev['is_past'] ? 'bg-surface-container text-outline-variant border-surface-dim' : 'bg-secondary-container text-on-secondary-container border-secondary-container' }}">
                                            {{ $ev['is_past'] ? 'Selesai' : 'Aktif' }}
                                        </span>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-4 text-xs">
                                        <span class="text-primary font-semibold uppercase bg-surface-container px-2 py-0.5 rounded">{{ $ev['role'] }}</span>
                                        <span class="text-outline-variant"><strong class="text-on-surface">{{ $ev['active_vacancies'] }}</strong> Lowongan Buka</span>
                                        <span class="text-outline-variant"><strong class="text-on-surface">{{ $ev['applicants'] }}</strong> Pelamar</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Upcoming Interviews & Collaborators -->
            <div class="flex flex-col gap-6">
                
                <!-- Upcoming Scheduled Interviews -->
                <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-surface-dim flex flex-col h-full overflow-hidden">
                    <div class="p-6 border-b border-surface-dim">
                        <h3 class="text-lg font-bold text-on-surface flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            Jadwal Wawancara Terdekat
                        </h3>
                    </div>
                    <div class="flex-1 overflow-auto">
                        @if(count($upcomingInterviews) === 0)
                            <div class="p-8 text-center text-outline-variant italic text-sm">Tidak ada jadwal wawancara yang akan datang.</div>
                        @else
                            <div class="divide-y divide-surface-dim">
                                @foreach($upcomingInterviews as $iv)
                                    <div class="p-4 hover:bg-surface-container-low transition-colors flex gap-4">
                                        <div class="w-12 shrink-0 text-center">
                                            <span class="block text-xl font-black text-primary">{{ \Carbon\Carbon::parse($iv['time'])->format('d') }}</span>
                                            <span class="block text-[10px] font-bold text-outline-variant uppercase">{{ \Carbon\Carbon::parse($iv['time'])->format('M') }}</span>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-on-surface">{{ $iv['candidate'] }}</h4>
                                            <p class="text-xs text-outline-variant mb-1">Divisi: {{ $iv['division'] }}</p>
                                            <div class="flex items-center gap-2 text-[10px] font-semibold text-primary uppercase mt-2">
                                                <span class="bg-surface-container px-2 py-0.5 rounded">{{ \Carbon\Carbon::parse($iv['time'])->format('H:i') }}</span>
                                                <span class="bg-surface-container px-2 py-0.5 rounded">{{ $iv['format'] ?? 'N/A' }}</span>
                                                @if($iv['location'])
                                                    <span class="bg-surface-container px-2 py-0.5 rounded max-w-[120px] truncate" title="{{ $iv['location'] }}">{{ $iv['location'] }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Collaborators Snapshot -->
                <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-surface-dim p-6">
                    <h3 class="text-lg font-bold text-on-surface flex items-center gap-2 mb-4">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Kolaborator & Tim
                    </h3>
                    @if(count($collaborators) === 0)
                        <p class="text-sm text-outline-variant italic">Anda belum memiliki tim kolaborator di event manapun.</p>
                    @else
                        <div class="flex flex-wrap gap-2">
                            @foreach(array_slice($collaborators, 0, 5) as $collab)
                                <div class="flex items-center gap-2 bg-surface-container border border-surface-dim rounded-full px-3 py-1.5" title="{{ $collab['event'] }}">
                                    <div class="w-6 h-6 rounded-full bg-primary text-white flex items-center justify-center text-xs font-bold">{{ substr($collab['name'], 0, 1) }}</div>
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-on-surface leading-none">{{ $collab['name'] }}</span>
                                        <span class="text-[9px] font-semibold text-outline-variant uppercase">{{ $collab['role'] }}</span>
                                    </div>
                                </div>
                            @endforeach
                            @if(count($collaborators) > 5)
                                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-surface-container border border-surface-dim text-xs font-bold text-outline-variant">
                                    +{{ count($collaborators) - 5 }}
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

            </div>
        </div>

    @endif
</div>
