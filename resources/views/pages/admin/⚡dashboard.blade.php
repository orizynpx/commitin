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
    // Search & Filter state for User Management
    public string $search = '';
    public string $roleFilter = 'all'; // all, student, organization, admin
    public bool $onlyBlocked = false;

    // Block reason inputs indexed by user_id
    public array $blockReasons = [];

    public function mount(): void
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Anda tidak memiliki hak akses ke halaman ini.');
        }
    }

    public function blockUser(string $userId): void
    {
        $this->validate([
            'blockReasons.' . $userId => ['required', 'string', 'max:255'],
        ], [
            'blockReasons.' . $userId . '.required' => 'Alasan pemblokiran wajib diisi.',
        ]);

        $user = User::findOrFail($userId);
        $user->update([
            'blocked_at' => now(),
            'block_reason' => $this->blockReasons[$userId],
        ]);

        unset($this->blockReasons[$userId]);
        session()->flash('status', "Pengguna \"{$user->name}\" berhasil dinonaktifkan.");
    }

    public function unblockUser(string $userId): void
    {
        $user = User::findOrFail($userId);
        $user->update([
            'blocked_at' => null,
            'block_reason' => null,
        ]);

        session()->flash('status', "Akun pengguna \"{$user->name}\" kembali diaktifkan.");
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
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-on-surface tracking-tight">{{ __('Pusat Kontrol Administratif') }}</h1>
            <p class="text-on-surface-variant text-sm mt-1">{{ __('Dashboard statistik utama, audit kegiatan, dan pengelolaan akun pengguna platform.') }}</p>
        </div>
    </div>

    <!-- Alert Status -->
    @if (session('status'))
        <div class="bg-surface-container border border-surface-dim text-on-surface rounded-xl p-4 text-sm flex items-center gap-2 shadow-sm">
            <svg class="w-5 h-5 text-primary flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <!-- 1. Platform Health Metrics (KPIs) -->
    <div class="space-y-4">
        <h2 class="text-xl font-bold text-on-surface flex items-center gap-2">
            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z"></path></svg>
            {{ __('Platform Health Metrics') }}
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Students vs Orgs -->
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

            <!-- Active Events -->
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

            <!-- Total Applications -->
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

            <!-- Skills Approved vs Pending -->
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

    <!-- 2 & 3 & 4. Vetting Queues -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Vetting Inbox (Organizations) -->
        <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-surface-dim p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-on-surface flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    Vetting Inbox (Org)
                </h3>
                @php
                    $pendingOrgsCount = DB::table('organization_profile')->where('verification_status', 'pending')->count();
                @endphp
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-secondary-container text-on-secondary-container">
                    {{ $pendingOrgsCount }} Pending
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
                                <span class="text-xs text-primary font-semibold uppercase tracking-wider">{{ str_replace('_', ' ', $org->organization_level) }}</span>
                            </div>
                            <div class="text-xs text-outline-variant">{{ \Carbon\Carbon::parse($org->created_at)->diffForHumans() }}</div>
                        </div>
                        <p class="text-sm text-on-surface-variant line-clamp-2 mb-3">{{ $org->description ?? 'Tidak ada deskripsi.' }}</p>
                        <div class="flex gap-2">
                            <button wire:click="approveOrganization('{{ $org->organization_profile_id }}')" class="bg-primary hover:bg-primary-container text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">Approve</button>
                            <button wire:click="rejectOrganization('{{ $org->organization_profile_id }}')" class="bg-surface-container hover:bg-surface-dim text-on-surface-variant text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">Reject</button>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-outline-variant italic text-sm">
                        Antrean verifikasi organisasi kosong.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Proposed Skills Moderation Inbox -->
        <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-surface-dim p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-on-surface flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                    Skills Moderation
                </h3>
                @php
                    $pendingSkillsCount = Skill::where('status', 'pending')->count();
                @endphp
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-secondary-container text-on-secondary-container">
                    {{ $pendingSkillsCount }} Pending
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
                            <button wire:click="approveSkill('{{ $skill->skill_id }}')" class="bg-primary hover:bg-primary-container text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">Approve</button>
                            <button wire:click="rejectSkill('{{ $skill->skill_id }}')" class="bg-surface-container hover:bg-surface-dim text-on-surface-variant text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">Reject</button>
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

    <!-- 5. Safety & Compliance Center -->
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-on-surface flex items-center gap-2">
                <svg class="w-5 h-5 text-error" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                Safety & Compliance Center
            </h2>
            <a href="#userManagementSection" class="text-sm font-semibold text-primary hover:underline">Lihat Semua Pengguna</a>
        </div>

        <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-surface-dim overflow-hidden">
            @php
                $blockedUsers = User::whereNotNull('blocked_at')->orderByDesc('blocked_at')->limit(5)->get();
            @endphp
            @if ($blockedUsers->isEmpty())
                <div class="p-8 text-center text-outline-variant italic text-sm">
                    Kondisi aman. Tidak ada pengguna yang sedang diblokir.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-error-container text-xs font-bold text-on-error-container uppercase tracking-wider">
                                <th class="px-6 py-4 whitespace-nowrap">Pengguna Diblokir</th>
                                <th class="px-6 py-4 whitespace-nowrap">Peran</th>
                                <th class="px-6 py-4 whitespace-nowrap">Waktu Blokir</th>
                                <th class="px-6 py-4 whitespace-nowrap">Alasan (Block Reason)</th>
                                <th class="px-6 py-4 text-right whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-dim text-sm">
                            @foreach ($blockedUsers as $bu)
                                <tr class="hover:bg-surface-container-low transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-bold text-on-surface">{{ $bu->name }}</div>
                                        <div class="text-xs text-outline-variant">{{ $bu->email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs font-semibold uppercase text-outline-variant">{{ $bu->role }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-on-surface-variant">{{ \Carbon\Carbon::parse($bu->blocked_at)->format('d M Y H:i') }}</td>
                                    <td class="px-6 py-4 text-on-surface-variant text-sm max-w-xs truncate" title="{{ $bu->block_reason }}">{{ $bu->block_reason }}</td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        <button wire:click="unblockUser('{{ $bu->user_id }}')" class="text-xs font-semibold text-primary hover:underline">Buka Blokir</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- User Management Section (Retained & Refined) -->
    <div id="userManagementSection" class="space-y-6 pt-10 border-t border-surface-dim">
        <h2 class="text-xl font-bold text-on-surface flex items-center gap-2">
            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
            </svg>
            {{ __('Daftar Seluruh Pengguna') }}
        </h2>

        <!-- Search and Filters -->
        <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-surface-dim p-5 flex flex-col md:flex-row gap-4 items-center justify-between">
            <div class="w-full md:flex-1 flex flex-col md:flex-row gap-4">
                <input 
                    type="text" 
                    wire:model.live="search" 
                    placeholder="Cari berdasarkan nama, email, NIM, atau tingkat..." 
                    class="w-full md:max-w-md border border-surface-dim rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                />
                
                <select 
                    wire:model.live="roleFilter" 
                    class="w-full md:w-48 border border-surface-dim rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                >
                    <option value="all">{{ __('Semua Peran') }}</option>
                    <option value="student">{{ __('Mahasiswa') }}</option>
                    <option value="organization">{{ __('Organisasi') }}</option>
                    <option value="admin">{{ __('Admin') }}</option>
                </select>
            </div>

            <label class="inline-flex items-center gap-2 text-sm text-on-surface-variant cursor-pointer self-start md:self-center">
                <input 
                    type="checkbox" 
                    wire:model.live="onlyBlocked" 
                    class="rounded border-surface-dim text-primary focus:ring-primary w-4 h-4"
                />
                <span class="font-medium">{{ __('Hanya Pengguna Diblokir') }}</span>
            </label>
        </div>

        @php
            $query = User::query()
                ->leftJoin('student_profile', 'users.user_id', '=', 'student_profile.user_id')
                ->leftJoin('organization_profile', 'users.user_id', '=', 'organization_profile.user_id')
                ->select('users.*', 'student_profile.student_id', 'organization_profile.organization_level')
                ->where('users.user_id', '!=', auth()->id());

            if ($search) {
                $searchTerm = '%' . $search . '%';
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('users.name', 'like', $searchTerm)
                      ->orWhere('users.email', 'like', $searchTerm)
                      ->orWhere('student_profile.student_id', 'like', $searchTerm)
                      ->orWhere('organization_profile.organization_level', 'like', $searchTerm);
                });
            }

            if ($roleFilter !== 'all') {
                $query->where('users.role', $roleFilter);
            }

            if ($onlyBlocked) {
                $query->whereNotNull('users.blocked_at');
            }

            $usersList = $query->orderBy('users.created_at', 'desc')->get();
        @endphp

        <!-- Users Table Card -->
        <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-surface-dim overflow-hidden">
            @if ($usersList->isEmpty())
                <div class="p-12 text-center text-outline-variant italic">
                    {{ __('Tidak ada pengguna yang cocok dengan kriteria pencarian.') }}
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-container border-b border-surface-dim text-xs font-bold text-on-surface uppercase tracking-wider">
                                <th class="px-6 py-4 whitespace-nowrap">{{ __('Nama & Email') }}</th>
                                <th class="px-6 py-4 whitespace-nowrap">{{ __('Peran') }}</th>
                                <th class="px-6 py-4 whitespace-nowrap">{{ __('Detail Profil') }}</th>
                                <th class="px-6 py-4 whitespace-nowrap">{{ __('Tanggal Join') }}</th>
                                <th class="px-6 py-4 whitespace-nowrap">{{ __('Status') }}</th>
                                <th class="px-6 py-4 text-right whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-dim text-sm">
                            @foreach ($usersList as $u)
                                <tr class="hover:bg-surface-container-low transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-bold text-on-surface">{{ $u->name }}</div>
                                        <div class="text-xs text-outline-variant">{{ $u->email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase 
                                            {{ $u->role === 'student' ? 'bg-surface-container text-primary border border-primary-fixed-dim' : '' }}
                                            {{ $u->role === 'organization' ? 'bg-secondary-container text-on-secondary-container border border-secondary-container' : '' }}
                                            {{ $u->role === 'admin' ? 'bg-surface-container text-on-surface' : '' }}
                                        ">
                                            {{ $u->role }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-on-surface-variant font-medium whitespace-nowrap">
                                        @if ($u->role === 'student')
                                            NIM: {{ $u->student_id ?: '-' }}
                                        @elseif ($u->role === 'organization')
                                            Tingkat: {{ ucfirst(str_replace('_', ' ', $u->organization_level)) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-outline-variant whitespace-nowrap">
                                        {{ $u->created_at->format('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($u->blocked_at)
                                            <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold uppercase bg-error-container text-error border border-error-container">
                                                {{ __('Diblokir') }}
                                            </span>
                                            <div class="text-[10px] text-error mt-1 max-w-[200px] truncate" title="{{ $u->block_reason }}">
                                                Alasan: {{ $u->block_reason }}
                                            </div>
                                        @else
                                            <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold uppercase bg-surface-container text-on-surface-variant">
                                                {{ __('Aktif') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        @if ($u->blocked_at)
                                            <button wire:click="unblockUser('{{ $u->user_id }}')" class="text-sm font-semibold text-primary hover:underline">
                                                {{ __('Aktifkan Kembali') }}
                                            </button>
                                        @elseif ($u->role !== 'admin')
                                            <div class="flex flex-col items-end gap-2">
                                                <input type="text" wire:model="blockReasons.{{ $u->user_id }}" placeholder="Tulis alasan..." class="w-32 md:w-40 text-xs border border-surface-dim rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-error" />
                                                <button wire:click="blockUser('{{ $u->user_id }}')" class="text-xs font-semibold bg-error text-white px-2 py-1 rounded hover:bg-error-container hover:text-error transition-colors">
                                                    {{ __('Blokir') }}
                                                </button>
                                            </div>
                                            @error('blockReasons.'.$u->user_id) <span class="text-[10px] text-error block mt-1">{{ $message }}</span> @enderror
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
</div>
