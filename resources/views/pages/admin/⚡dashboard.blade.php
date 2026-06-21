<?php

use App\Models\User;
use App\Models\Event;
use App\Models\Vacancy;
use App\Models\Skill;
use App\Models\VacancyApplication;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component
{
    public function mount(): void
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Anda tidak memiliki hak akses ke halaman ini.');
        }
    }

    public function deleteEvent(string $eventId): void
    {
        $event = Event::findOrFail($eventId);
        $name = $event->event_name;
        $event->delete();

        session()->flash('status', "Event \"{$name}\" beserta lowongan terkait berhasil dihapus.");
    }

    public function deleteVacancy(string $vacancyId): void
    {
        $vacancy = Vacancy::findOrFail($vacancyId);
        $division = $vacancy->division;
        $vacancy->delete();

        session()->flash('status', "Lowongan divisi \"{$division}\" berhasil dihapus.");
    }

    public function approveOrganization(string $orgProfileId): void
    {
        DB::table('organization_profile')->where('organization_profile_id', $orgProfileId)->update([
            'verification_status' => 'verified',
            'verified_at' => now(),
        ]);
        session()->flash('status', "Profil organisasi berhasil diverifikasi.");
    }

    public function rejectOrganization(string $orgProfileId): void
    {
        DB::table('organization_profile')->where('organization_profile_id', $orgProfileId)->update([
            'verification_status' => 'rejected',
            'verified_at' => null,
        ]);
        session()->flash('status', "Verifikasi profil organisasi ditolak.");
    }

    public function approveSkill(string $skillId): void
    {
        DB::table('skills')->where('skill_id', $skillId)->update(['status' => 'approved']);
        session()->flash('status', "Keahlian baru berhasil disetujui.");
    }

    public function rejectSkill(string $skillId): void
    {
        DB::table('skills')->where('skill_id', $skillId)->update(['status' => 'rejected']);
        session()->flash('status', "Keahlian usulan berhasil ditolak.");
    }
}; ?>

<div class="space-y-10 py-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-on-surface tracking-tight">{{ __('Pusat Kontrol Administratif') }}</h1>
            <p class="text-on-surface-variant text-sm mt-1">{{ __('Dashboard statistik utama dan audit kegiatan platform.') }}</p>
        </div>
    </div>

    @if (session('status'))
        <div class="bg-surface-container border border-surface-dim text-on-surface rounded-xl p-4 text-sm flex items-center gap-2 shadow-sm">
            <svg class="w-5 h-5 text-primary flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <div class="space-y-4">
        <h2 class="text-xl font-bold text-on-surface flex items-center gap-2">
            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z"></path></svg>
            {{ __('Statistik Platform') }}
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-surface-dim p-5">
                <div class="flex justify-between items-start mb-4">
                    <div class="p-3 bg-surface-container text-primary rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <span class="text-xs font-bold text-outline-variant uppercase">PENGGUNA</span>
                </div>
                <div class="flex items-end gap-2">
                    <strong class="text-2xl text-on-surface">{{ User::where('role', 'student')->count() }}</strong>
                    <span class="text-sm text-on-surface-variant mb-1">Mhs</span>
                    <span class="text-outline-variant mb-1">|</span>
                    <strong class="text-2xl text-on-surface">{{ User::where('role', 'organization')->count() }}</strong>
                    <span class="text-sm text-on-surface-variant mb-1">Org</span>
                </div>
            </div>

            <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-surface-dim p-5">
                <div class="flex justify-between items-start mb-4">
                    <div class="p-3 bg-surface-container text-primary rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <span class="text-xs font-bold text-outline-variant uppercase">EVENT AKTIF</span>
                </div>
                <div class="flex items-end gap-2">
                    <strong class="text-2xl text-on-surface">{{ Event::where('is_official', true)->count() }}</strong>
                    <span class="text-sm text-on-surface-variant mb-1">Resmi</span>
                    <span class="text-outline-variant mb-1">|</span>
                    <strong class="text-2xl text-on-surface">{{ Event::where('is_official', false)->count() }}</strong>
                    <span class="text-sm text-on-surface-variant mb-1">Non-Resmi</span>
                </div>
            </div>

            <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-surface-dim p-5">
                <div class="flex justify-between items-start mb-4">
                    <div class="p-3 bg-surface-container text-primary rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <span class="text-xs font-bold text-outline-variant uppercase">APLIKASI DB</span>
                </div>
                <div class="flex items-end gap-2">
                    <strong class="text-2xl text-on-surface">{{ VacancyApplication::count() }}</strong>
                    <span class="text-sm text-on-surface-variant mb-1">Total Lamaran</span>
                </div>
            </div>

            <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-surface-dim p-5">
                <div class="flex justify-between items-start mb-4">
                    <div class="p-3 bg-surface-container text-primary rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                    </div>
                    <span class="text-xs font-bold text-outline-variant uppercase">DIREKTORI SKILL</span>
                </div>
                <div class="flex items-end gap-2">
                    <strong class="text-2xl text-on-surface">{{ Skill::where('status', 'approved')->count() }}</strong>
                    <span class="text-sm text-on-surface-variant mb-1">Approv</span>
                    <span class="text-outline-variant mb-1">|</span>
                    <strong class="text-2xl text-on-surface">{{ Skill::where('status', 'pending')->count() }}</strong>
                    <span class="text-sm text-on-surface-variant mb-1">Pending</span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-surface-dim p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-on-surface flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    Persetujuan Verifikasi Ormawa
                </h3>
                @php
                    $pendingOrgsCount = DB::table('organization_profile')->where('verification_status', 'pending')->count();
                @endphp
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-secondary-container text-on-secondary-container">
                    {{ $pendingOrgsCount }} Tertunda
                </span>
            </div>
            
            <div class="divide-y divide-surface-dim">
                @php
                    $pendingOrgs = DB::table('organization_profile')
                        ->join('users', 'users.user_id', '=', 'organization_profile.user_id')
                        ->where('verification_status', 'pending')
                        ->select('organization_profile.*', 'users.name')
                        ->orderBy('organization_profile.created_at', 'asc')
                        ->limit(5)
                        ->get();
                @endphp
                
                @forelse ($pendingOrgs as $org)
                    <div class="py-4">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h4 class="font-bold text-on-surface">{{ $org->name }}</h4>
                                <span class="text-xs text-primary font-semibold uppercase tracking-wider">{{ $org->organization_level === 'study_program' ? 'Program Studi' : ($org->organization_level === 'faculty' ? 'Fakultas' : ($org->organization_level === 'university' ? 'Universitas' : str_replace('_', ' ', $org->organization_level))) }}</span>
                            </div>
                            <div class="text-xs text-outline-variant">{{ \Carbon\Carbon::parse($org->created_at)->diffForHumans() }}</div>
                        </div>
                        <p class="text-sm text-on-surface-variant line-clamp-2 mb-3">{{ $org->description ?? 'Tidak ada deskripsi.' }}</p>
                        <div class="flex gap-2">
                            <button wire:click="approveOrganization('{{ $org->organization_profile_id }}')" class="bg-primary hover:bg-primary-container text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">Setujui</button>
                            <button wire:click="rejectOrganization('{{ $org->organization_profile_id }}')" class="bg-surface-container hover:bg-surface-dim text-on-surface-variant text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">Tolak</button>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-outline-variant italic text-sm">
                        Antrean verifikasi organisasi kosong.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-surface-dim p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-on-surface flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                    Moderasi Usulan Keahlian
                </h3>
                @php
                    $pendingSkillsCount = Skill::where('status', 'pending')->count();
                @endphp
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-secondary-container text-on-secondary-container">
                    {{ $pendingSkillsCount }} Tertunda
                </span>
            </div>

            <div class="divide-y divide-surface-dim">
                @php
                    $pendingSkills = Skill::where('status', 'pending')
                        ->orderBy('created_at', 'asc')
                        ->limit(5)
                        ->get();
                @endphp
                
                @forelse ($pendingSkills as $skill)
                    <div class="py-4 flex items-center justify-between">
                        <div>
                            <h4 class="font-bold text-on-surface">{{ $skill->skill_name }}</h4>
                            <span class="text-xs text-outline-variant">Diusulkan {{ $skill->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="flex gap-2">
                            <button wire:click="approveSkill('{{ $skill->skill_id }}')" class="bg-primary hover:bg-primary-container text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">Setujui</button>
                            <button wire:click="rejectSkill('{{ $skill->skill_id }}')" class="bg-surface-container hover:bg-surface-dim text-on-surface-variant text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">Tolak</button>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-outline-variant italic text-sm">
                        Antrean usulan keahlian kosong.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
