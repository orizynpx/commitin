<?php

use App\Models\User;
use App\Models\Event;
use App\Models\Vacancy;
use App\Models\Skill;
use App\Models\VacancyApplication;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component
{
    // Search & Filter state
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
}; ?>

<div class="space-y-10 py-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-900 tracking-tight">{{ __('Pusat Kontrol Administratif') }}</h1>
            <p class="text-slate-500 text-sm mt-1">{{ __('Dashboard statistik utama, audit kegiatan, dan pengelolaan akun pengguna platform.') }}</p>
        </div>
    </div>

    <!-- Alert Status -->
    @if (session('status'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl p-4 text-sm flex items-center gap-2 shadow-sm">
            <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <!-- Statistics Section -->
    <div class="space-y-6">
        <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z"></path>
            </svg>
            {{ __('Statistik Platform') }}
        </h2>
        
        <!-- Platform Stats Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5 flex items-center gap-4">
                <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                <div>
                    <span class="text-xs text-slate-400 font-semibold block uppercase">{{ __('Mahasiswa') }}</span>
                    <strong class="text-2xl text-slate-800">{{ User::where('role', 'student')->count() }}</strong>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5 flex items-center gap-4">
                <div class="p-3 bg-indigo-50 text-indigo-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <div>
                    <span class="text-xs text-slate-400 font-semibold block uppercase">{{ __('Organisasi') }}</span>
                    <strong class="text-2xl text-slate-800">{{ User::where('role', 'organization')->count() }}</strong>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5 flex items-center gap-4">
                <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div>
                    <span class="text-xs text-slate-400 font-semibold block uppercase">{{ __('Event') }}</span>
                    <strong class="text-2xl text-slate-800">{{ Event::count() }}</strong>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5 flex items-center gap-4">
                <div class="p-3 bg-amber-50 text-amber-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div>
                    <span class="text-xs text-slate-400 font-semibold block uppercase">{{ __('Lowongan') }}</span>
                    <strong class="text-2xl text-slate-800">{{ Vacancy::count() }}</strong>
                </div>
            </div>
        </div>

        <!-- Application Status Panel -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4">{{ __('Status Lamaran Lowongan') }}</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-slate-50 rounded-xl p-4 text-center border border-slate-100">
                    <span class="text-xs font-semibold text-amber-600 uppercase block mb-1">Pending</span>
                    <strong class="text-xl text-slate-800">{{ VacancyApplication::where('status', 'pending')->count() }}</strong>
                </div>
                <div class="bg-slate-50 rounded-xl p-4 text-center border border-slate-100">
                    <span class="text-xs font-semibold text-blue-600 uppercase block mb-1">Interviewing</span>
                    <strong class="text-xl text-slate-800">{{ VacancyApplication::where('status', 'interviewing')->count() }}</strong>
                </div>
                <div class="bg-slate-50 rounded-xl p-4 text-center border border-slate-100">
                    <span class="text-xs font-semibold text-emerald-600 uppercase block mb-1">Diterima</span>
                    <strong class="text-xl text-slate-800">{{ VacancyApplication::where('status', 'accepted')->count() }}</strong>
                </div>
                <div class="bg-slate-50 rounded-xl p-4 text-center border border-slate-100">
                    <span class="text-xs font-semibold text-red-600 uppercase block mb-1">Ditolak</span>
                    <strong class="text-xl text-slate-800">{{ VacancyApplication::where('status', 'rejected')->count() }}</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- User Management Section -->
    <div class="space-y-6">
        <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
            </svg>
            {{ __('Manajemen Akun Pengguna') }}
        </h2>

        <!-- Search and Filters -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 flex flex-col md:flex-row gap-4 items-center justify-between">
            <div class="w-full md:flex-1 flex flex-col md:flex-row gap-4">
                <input 
                    type="text" 
                    wire:model.live="search" 
                    placeholder="Cari berdasarkan nama, email, NIM, atau tingkat..." 
                    class="w-full md:max-w-md border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                
                <select 
                    wire:model.live="roleFilter" 
                    class="w-full md:w-48 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="all">{{ __('Semua Peran') }}</option>
                    <option value="student">{{ __('Mahasiswa') }}</option>
                    <option value="organization">{{ __('Organisasi') }}</option>
                    <option value="admin">{{ __('Admin') }}</option>
                </select>
            </div>

            <label class="inline-flex items-center gap-2 text-sm text-slate-600 cursor-pointer self-start md:self-center">
                <input 
                    type="checkbox" 
                    wire:model.live="onlyBlocked" 
                    class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 w-4 h-4"
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
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            @if ($usersList->isEmpty())
                <div class="p-12 text-center text-slate-450 italic">
                    {{ __('Tidak ada pengguna yang cocok dengan kriteria pencarian.') }}
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                <th class="px-6 py-4">{{ __('Nama & Email') }}</th>
                                <th class="px-6 py-4">{{ __('Peran') }}</th>
                                <th class="px-6 py-4">{{ __('Detail Profil') }}</th>
                                <th class="px-6 py-4">{{ __('Tanggal Join') }}</th>
                                <th class="px-6 py-4">{{ __('Status') }}</th>
                                <th class="px-6 py-4 text-right">{{ __('Tindakan Pemblokiran') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @foreach ($usersList as $u)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-900">{{ $u->name }}</div>
                                        <div class="text-xs text-slate-400">{{ $u->email }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase 
                                            {{ $u->role === 'student' ? 'bg-blue-50 text-blue-700 border border-blue-100' : '' }}
                                            {{ $u->role === 'organization' ? 'bg-purple-50 text-purple-700 border border-purple-100' : '' }}
                                            {{ $u->role === 'admin' ? 'bg-slate-100 text-slate-800' : '' }}
                                        ">
                                            {{ $u->role }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600 font-medium">
                                        @if ($u->role === 'student')
                                            NIM: {{ $u->student_id ?: '-' }}
                                        @elseif ($u->role === 'organization')
                                            Tingkat: {{ ucfirst(str_replace('_', ' ', $u->organization_level)) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-slate-500">
                                        {{ $u->created_at->format('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($u->blocked_at)
                                            <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold uppercase bg-red-50 text-red-700 border border-red-100">
                                                {{ __('Diblokir') }}
                                            </span>
                                            <div class="text-[10px] text-red-500 mt-1 max-w-[200px] truncate" title="{{ $u->block_reason }}">
                                                Alasan: {{ $u->block_reason }}
                                            </div>
                                        @else
                                            <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase bg-emerald-50 text-emerald-700 border border-emerald-100">
                                                {{ __('Aktif') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if ($u->blocked_at)
                                            <button 
                                                onclick="confirm('Buka blokir pengguna ini?') || event.stopImmediatePropagation()" 
                                                wire:click="unblockUser('{{ $u->user_id }}')"
                                                class="text-xs bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-3 py-1.5 rounded-lg transition-colors shadow-sm"
                                            >
                                                {{ __('Aktifkan Kembali') }}
                                            </button>
                                        @else
                                            <form 
                                                wire:submit.prevent="blockUser('{{ $u->user_id }}')" 
                                                class="inline-flex gap-2 items-center"
                                            >
                                                <input 
                                                    type="text" 
                                                    wire:model="blockReasons.{{ $u->user_id }}" 
                                                    placeholder="Tulis alasan..." 
                                                    class="border border-slate-200 rounded-lg px-2.5 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-red-500" 
                                                    required 
                                                />
                                                <button 
                                                    type="submit"
                                                    class="text-xs bg-red-600 hover:bg-red-700 text-white font-semibold px-3 py-1.5 rounded-lg transition-colors shadow-sm"
                                                >
                                                    {{ __('Blokir') }}
                                                </button>
                                            </form>
                                            @error('blockReasons.' . $u->user_id)
                                                <div class="text-[10px] text-red-500 mt-1 text-right">{{ $message }}</div>
                                            @enderror
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

    <!-- Events Auditing Section -->
    <div class="space-y-6">
        <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            {{ __('Audit Event Kepanitiaan') }}
        </h2>

        @php
            $eventsList = Event::orderBy('created_at', 'desc')->get();
        @endphp

        <!-- Events List Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            @if ($eventsList->isEmpty())
                <div class="p-12 text-center text-slate-450 italic">
                    {{ __('Tidak ada event yang terdaftar di platform.') }}
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                <th class="px-6 py-4">{{ __('Nama Event') }}</th>
                                <th class="px-6 py-4">{{ __('Tanggal Pelaksanaan') }}</th>
                                <th class="px-6 py-4">{{ __('Sifat Event') }}</th>
                                <th class="px-6 py-4 text-right">{{ __('Tindakan') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @foreach ($eventsList as $e)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-4 font-bold text-slate-900">
                                        {{ $e->event_name }}
                                    </td>
                                    <td class="px-6 py-4 text-slate-500">
                                        {{ $e->event_date->format('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold 
                                            {{ $e->is_official ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-slate-100 text-slate-800' }}
                                        ">
                                            {{ $e->is_official ? __('Resmi Kampus') : __('Informal / Mahasiswa') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button 
                                            onclick="confirm('Apakah Anda yakin ingin menghapus event ini secara permanen? Semua lowongan dan berkas lamaran terkait akan ikut terhapus.') || event.stopImmediatePropagation()" 
                                            wire:click="deleteEvent('{{ $e->event_id }}')"
                                            class="text-xs bg-red-600 hover:bg-red-700 text-white font-semibold px-3 py-1.5 rounded-lg transition-colors shadow-sm"
                                        >
                                            {{ __('Hapus Event') }}
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Vacancies Auditing Section -->
    <div class="space-y-6">
        <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
            {{ __('Audit Lowongan Divisi') }}
        </h2>

        @php
            $vacanciesList = Vacancy::with('event')->orderBy('created_at', 'desc')->get();
        @endphp

        <!-- Vacancies List Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            @if ($vacanciesList->isEmpty())
                <div class="p-12 text-center text-slate-450 italic">
                    {{ __('Tidak ada lowongan divisi yang terbit saat ini.') }}
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                <th class="px-6 py-4">{{ __('Event') }}</th>
                                <th class="px-6 py-4">{{ __('Divisi Kepanitiaan') }}</th>
                                <th class="px-6 py-4">{{ __('Status Lowongan') }}</th>
                                <th class="px-6 py-4 text-right">{{ __('Tindakan') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @foreach ($vacanciesList as $v)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-4 font-bold text-slate-900">
                                        {{ $v->event ? $v->event->event_name : '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-slate-600 font-semibold">
                                        {{ $v->division }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase 
                                            {{ $v->status === 'OPEN' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-red-50 text-red-700 border border-red-100' }}
                                        ">
                                            {{ $v->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button 
                                            onclick="confirm('Apakah Anda yakin ingin menghapus lowongan divisi ini?') || event.stopImmediatePropagation()" 
                                            wire:click="deleteVacancy('{{ $v->vacancy_id }}')"
                                            class="text-xs bg-red-600 hover:bg-red-700 text-white font-semibold px-3 py-1.5 rounded-lg transition-colors shadow-sm"
                                        >
                                            {{ __('Hapus Lowongan') }}
                                        </button>
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
