<?php

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component
{
    public string $search = '';
    public string $roleFilter = 'all';

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
}; ?>

@section('title', 'Manajemen Pengguna')

<div class="space-y-10 py-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-on-surface tracking-tight">{{ __('Manajemen Pengguna') }}</h1>
            <p class="text-on-surface-variant text-sm mt-1">{{ __('Kelola hak akses pengguna, pemblokiran akun, dan pemulihan status akun.') }}</p>
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

    <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-surface-dim p-5 flex flex-col md:flex-row gap-4 items-center justify-between">
        <div class="w-full md:flex-1 flex flex-col md:flex-row gap-4">
            <input 
                type="text" 
                wire:model.live="search" 
                placeholder="Cari berdasarkan nama, email, NIM..." 
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
    </div>

    @php
        $queryActive = User::query()
            ->leftJoin('student_profile', 'users.user_id', '=', 'student_profile.user_id')
            ->leftJoin('organization_profile', 'users.user_id', '=', 'organization_profile.user_id')
            ->select('users.*', 'student_profile.student_id', 'organization_profile.organization_level')
            ->where('users.user_id', '!=', auth()->id())
            ->whereNull('users.blocked_at');

        $queryBlocked = User::query()
            ->leftJoin('student_profile', 'users.user_id', '=', 'student_profile.user_id')
            ->leftJoin('organization_profile', 'users.user_id', '=', 'organization_profile.user_id')
            ->select('users.*', 'student_profile.student_id', 'organization_profile.organization_level')
            ->where('users.user_id', '!=', auth()->id())
            ->whereNotNull('users.blocked_at');

        if ($search) {
            $searchTerm = '%' . $search . '%';
            $filterFunc = function ($q) use ($searchTerm) {
                $q->where('users.name', 'like', $searchTerm)
                  ->orWhere('users.email', 'like', $searchTerm)
                  ->orWhere('student_profile.student_id', 'like', $searchTerm)
                  ->orWhere('organization_profile.organization_level', 'like', $searchTerm);
            };
            $queryActive->where($filterFunc);
            $queryBlocked->where($filterFunc);
        }

        if ($roleFilter !== 'all') {
            $queryActive->where('users.role', $roleFilter);
            $queryBlocked->where('users.role', $roleFilter);
        }

        $activeUsers = $queryActive->orderBy('users.created_at', 'desc')->get();
        $blockedUsers = $queryBlocked->orderBy('users.created_at', 'desc')->get();
    @endphp

    <div class="space-y-6">
        <h2 class="text-xl font-bold text-on-surface flex items-center gap-2">
            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            {{ __('Pengguna Aktif') }}
        </h2>
        
        <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-surface-dim overflow-hidden">
            @if ($activeUsers->isEmpty())
                <div class="p-8 text-center text-outline-variant italic text-sm">
                    Tidak ada pengguna aktif yang cocok dengan kriteria.
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
                                <th class="px-6 py-4 text-right whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-dim text-sm">
                            @foreach ($activeUsers as $u)
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
                                            {{ $u->role === 'student' ? 'Mahasiswa' : ($u->role === 'organization' ? 'Organisasi' : $u->role) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-on-surface-variant font-medium whitespace-nowrap">
                                        @if ($u->role === 'student')
                                            NIM: {{ $u->student_id ?: '-' }}
                                        @elseif ($u->role === 'organization')
                                            Tingkat: {{ $u->organization_level === 'study_program' ? 'Program Studi' : ($u->organization_level === 'faculty' ? 'Fakultas' : ($u->organization_level === 'university' ? 'Universitas' : str_replace('_', ' ', $u->organization_level))) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-outline-variant whitespace-nowrap">
                                        {{ $u->created_at->format('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap relative">
                                        @if ($u->role !== 'admin')
                                            <div x-data="{ open: false }" class="relative inline-block text-left">
                                                <button @click="open = !open" class="text-gray-500 hover:text-gray-700 focus:outline-none text-xl font-bold px-2">
                                                    &#8942;
                                                </button>
                                                <div x-show="open" @click.away="open = false" style="display: none;" class="absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-surface-container-lowest ring-1 ring-black ring-opacity-5 z-20 p-4 border border-surface-dim text-left">
                                                    <form wire:submit.prevent="blockUser('{{ $u->user_id }}')" class="space-y-2">
                                                        <label class="block text-xs font-semibold text-on-surface mb-1">Alasan Pemblokiran:</label>
                                                        <input type="text" wire:model="blockReasons.{{ $u->user_id }}" placeholder="Tulis alasan..." class="w-full text-xs border border-surface-dim rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-error bg-surface-container-lowest text-on-surface" />
                                                        @error('blockReasons.'.$u->user_id) <span class="text-[10px] text-error block">{{ $message }}</span> @enderror
                                                        <button type="submit" class="w-full text-center text-xs font-semibold bg-error text-white py-1 rounded hover:bg-error-container hover:text-error transition-colors">
                                                            {{ __('Blokir Pengguna') }}
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        @else
                                            -
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

    <div class="space-y-6">
        <h2 class="text-xl font-bold text-error flex items-center gap-2">
            <svg class="w-5 h-5 text-error" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            {{ __('Pengguna Diblokir') }}
        </h2>
        
        <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-surface-dim overflow-hidden">
            @if ($blockedUsers->isEmpty())
                <div class="p-8 text-center text-outline-variant italic text-sm">
                    Kondisi aman. Tidak ada pengguna yang diblokir.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-error-container text-xs font-bold text-on-error-container uppercase tracking-wider">
                                <th class="px-6 py-4 whitespace-nowrap">{{ __('Nama & Email') }}</th>
                                <th class="px-6 py-4 whitespace-nowrap">{{ __('Peran') }}</th>
                                <th class="px-6 py-4 whitespace-nowrap">{{ __('Waktu Blokir') }}</th>
                                <th class="px-6 py-4 whitespace-nowrap">{{ __('Alasan (Block Reason)') }}</th>
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
                                    <td class="px-6 py-4 whitespace-nowrap text-xs font-semibold uppercase text-outline-variant">
                                        {{ $bu->role === 'student' ? 'Mahasiswa' : ($bu->role === 'organization' ? 'Organisasi' : $bu->role) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-on-surface-variant">{{ \Carbon\Carbon::parse($bu->blocked_at)->format('d M Y H:i') }}</td>
                                    <td class="px-6 py-4 text-on-surface-variant text-sm max-w-xs truncate" title="{{ $bu->block_reason }}">{{ $bu->block_reason }}</td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        <div x-data="{ open: false }" class="relative inline-block text-left">
                                            <button @click="open = !open" class="text-gray-500 hover:text-gray-700 focus:outline-none text-xl font-bold px-2">
                                                &#8942;
                                            </button>
                                            <div x-show="open" @click.away="open = false" style="display: none;" class="absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-surface-container-lowest ring-1 ring-black ring-opacity-5 z-20 p-4 border border-surface-dim text-left">
                                                <button wire:click="unblockUser('{{ $bu->user_id }}')" class="w-full text-center text-xs font-semibold bg-primary text-white py-1.5 rounded hover:bg-primary-container transition-colors">
                                                    {{ __('Aktifkan Kembali') }}
                                                </button>
                                            </div>
                                        </div>
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
