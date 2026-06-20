<?php

use App\Models\OrganizationProfile;
use App\Models\User;
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

    public function approve(string $profileId): void
    {
        $profile = OrganizationProfile::findOrFail($profileId);
        $profile->update([
            'verification_status' => 'verified',
            'verified_at' => now(),
        ]);

        session()->flash('status', "Organisasi \"{$profile->user->name}\" berhasil disetujui!");
    }

    public function reject(string $userId): void
    {
        $user = User::findOrFail($userId);
        $name = $user->name;
        
        $user->delete();

        session()->flash('status', "Organisasi \"{$name}\" ditolak dan akun telah dihapus.");
    }
}; ?>

<div class="space-y-8 py-6">
    <!-- Header -->
    <div>
        <h1 class="text-3xl font-bold text-slate-900 tracking-tight">{{ __('Persetujuan Akun Organisasi (Ormawa)') }}</h1>
        <p class="text-slate-500 text-sm mt-1">{{ __('Verifikasi dan tinjau pendaftaran ormawa baru sebelum mereka dapat mempublikasikan event.') }}</p>
    </div>

    <!-- Alert status -->
    @if (session('status'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl p-4 text-sm flex items-center gap-2 shadow-sm">
            <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    @php
        $pendingProfiles = OrganizationProfile::where('verification_status', 'pending')
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    @endphp

    <!-- Vetting Desk List -->
    <div class="space-y-4">
        @if ($pendingProfiles->isEmpty())
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-12 text-center">
                <svg class="w-16 h-16 text-slate-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="text-lg font-bold text-slate-900 mb-1">{{ __('Semua Bersih!') }}</h3>
                <p class="text-slate-500 text-sm">{{ __('Tidak ada pendaftaran organisasi baru yang memerlukan persetujuan saat ini.') }}</p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-6">
                @foreach ($pendingProfiles as $profile)
                    @if ($profile->user)
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden flex flex-col justify-between" x-data="{ expanded: false }">
                            <!-- Card Header -->
                            <div class="p-6 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-50 bg-slate-50/20">
                                <div class="flex items-center gap-4">
                                    <!-- Avatar initials -->
                                    <img 
                                        src="https://ui-avatars.com/api/?name={{ urlencode($profile->user->name) }}&background=6366f1&color=fff&size=48&bold=true" 
                                        alt="{{ $profile->user->name }}" 
                                        class="w-12 h-12 rounded-xl border border-indigo-100 object-cover flex-shrink-0"
                                    />
                                    <div>
                                        <h3 class="text-lg font-bold text-slate-900 leading-snug">{{ $profile->user->name }}</h3>
                                        <p class="text-xs text-slate-400 mt-0.5">{{ $profile->user->email }} &bull; Terdaftar pada {{ $profile->created_at->format('d M Y H:i') }}</p>
                                    </div>
                                </div>

                                <!-- Organization Level Badges -->
                                <div class="flex items-center gap-2">
                                    @php
                                        $level = $profile->organization_level;
                                        $badgeClasses = '';
                                        if ($level === 'study_program') {
                                            $badgeClasses = 'bg-purple-50 text-purple-700 border border-purple-100';
                                            $levelLabel = 'Himpunan Prodi';
                                        } elseif ($level === 'faculty') {
                                            $badgeClasses = 'bg-blue-50 text-blue-700 border border-blue-100';
                                            $levelLabel = 'BEM / DPM Fakultas';
                                        } else {
                                            $badgeClasses = 'bg-green-50 text-green-700 border border-green-100';
                                            $levelLabel = 'UKM / Ormawa Universitas';
                                        }
                                    @endphp
                                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider {{ $badgeClasses }}">
                                        {{ $levelLabel }}
                                    </span>
                                </div>
                            </div>

                            <!-- Description/Profile details section -->
                            <div class="p-6 space-y-4">
                                <div class="text-sm font-semibold text-slate-700">{{ __('Deskripsi & Profil Kegiatan:') }}</div>
                                <div 
                                    class="bg-slate-50 rounded-xl p-4 text-sm text-slate-600 leading-relaxed transition-all duration-300"
                                    :class="expanded ? '' : 'line-clamp-2'"
                                >
                                    {{ $profile->description ?: 'Tidak ada deskripsi profil ormawa.' }}
                                </div>
                                
                                @if (strlen($profile->description) > 150)
                                    <button 
                                        type="button" 
                                        @click="expanded = !expanded" 
                                        class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold focus:outline-none flex items-center gap-1"
                                    >
                                        <span x-text="expanded ? 'Sembunyikan Deskripsi' : 'Baca Selengkapnya'"></span>
                                    </button>
                                @endif
                            </div>

                            <!-- Card Actions -->
                            <div class="p-6 border-t border-slate-50 bg-slate-50/10 flex justify-end gap-3">
                                <button 
                                    onclick="confirm('Apakah Anda yakin ingin menolak dan menghapus akun organisasi ini?') || event.stopImmediatePropagation()" 
                                    wire:click="reject('{{ $profile->user_id }}')"
                                    class="bg-white hover:bg-red-50 text-red-600 border border-slate-200 hover:border-red-200 px-4 py-2.5 rounded-xl text-xs font-semibold transition-colors"
                                >
                                    {{ __('Tolak & Hapus') }}
                                </button>
                                <button 
                                    wire:click="approve('{{ $profile->organization_profile_id }}')"
                                    class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-xl text-xs font-semibold transition-colors shadow-sm shadow-emerald-100"
                                >
                                    {{ __('Setujui Pendaftaran') }}
                                </button>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
</div>
