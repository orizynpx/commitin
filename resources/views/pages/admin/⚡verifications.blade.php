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
        <h1 class="text-3xl font-bold text-on-surface tracking-tight">{{ __('Persetujuan Akun Organisasi (Ormawa)') }}</h1>
        <p class="text-outline-variant text-sm mt-1">{{ __('Verifikasi dan tinjau pendaftaran ormawa baru sebelum mereka dapat mempublikasikan event.') }}</p>
    </div>

    <!-- Alert status -->
    @if (session('status'))
        <div class="bg-surface-container border border-surface-dim text-on-surface rounded-xl p-4 text-sm flex items-center gap-2 shadow-sm">
            <svg class="w-5 h-5 text-primary flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            <div class="bg-surface-container-lowest rounded-2xl border border-surface-dim shadow-sm p-12 text-center">
                <svg class="w-16 h-16 text-slate-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="text-lg font-bold text-on-surface mb-1">{{ __('Semua Bersih!') }}</h3>
                <p class="text-outline-variant text-sm">{{ __('Tidak ada pendaftaran organisasi baru yang memerlukan persetujuan saat ini.') }}</p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-6">
                @foreach ($pendingProfiles as $profile)
                    @if ($profile->user)
                        <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-surface-dim overflow-hidden flex flex-col justify-between" x-data="{ expanded: false }">
                            <!-- Card Header -->
                            <div class="p-6 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-surface-dim bg-surface-container/20">
                                <div class="flex items-center gap-4">
                                    <!-- Avatar initials -->
                                    <img 
                                        src="https://ui-avatars.com/api/?name={{ urlencode($profile->user->name) }}&background=6366f1&color=fff&size=48&bold=true" 
                                        alt="{{ $profile->user->name }}" 
                                        class="w-12 h-12 rounded-xl border border-primary-fixed-dim object-cover flex-shrink-0"
                                    />
                                    <div>
                                        <h3 class="text-lg font-bold text-on-surface leading-snug">{{ $profile->user->name }}</h3>
                                        <p class="text-xs text-outline-variant mt-0.5">{{ $profile->user->email }} &bull; Terdaftar pada {{ $profile->created_at->format('d M Y H:i') }}</p>
                                    </div>
                                </div>

                                <!-- Organization Level Badges -->
                                <div class="flex items-center gap-2">
                                    @php
                                        $level = $profile->organization_level;
                                        $badgeClasses = '';
                                        if ($level === 'study_program') {
                                            $badgeClasses = 'bg-secondary-container text-on-secondary-container border border-secondary-container';
                                            $levelLabel = 'Himpunan Prodi';
                                        } elseif ($level === 'faculty') {
                                            $badgeClasses = 'bg-surface-container text-primary border border-primary-fixed-dim';
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
                                <div class="text-sm font-semibold text-on-surface-variant">{{ __('Deskripsi & Profil Kegiatan:') }}</div>
                                <div 
                                    class="bg-surface-container rounded-xl p-4 text-sm text-on-surface-variant leading-relaxed transition-all duration-300"
                                    :class="expanded ? '' : 'line-clamp-2'"
                                >
                                    {{ $profile->description ?: 'Tidak ada deskripsi profil ormawa.' }}
                                </div>
                                
                                @if (strlen($profile->description) > 150)
                                    <button 
                                        type="button" 
                                        @click="expanded = !expanded" 
                                        class="text-xs text-primary hover:text-on-primary-container font-semibold focus:outline-none flex items-center gap-1"
                                    >
                                        <span x-text="expanded ? 'Sembunyikan Deskripsi' : 'Baca Selengkapnya'"></span>
                                    </button>
                                @endif
                            </div>

                            <!-- Card Actions -->
                            <div class="p-6 border-t border-surface-dim bg-surface-container/10 flex justify-end gap-3">
                                <button 
                                    onclick="confirm('Apakah Anda yakin ingin menolak dan menghapus akun organisasi ini?') || event.stopImmediatePropagation()" 
                                    wire:click="reject('{{ $profile->user_id }}')"
                                    class="bg-surface-container-lowest hover:bg-error-container text-error border border-surface-dim hover:border-error-container px-4 py-2.5 rounded-xl text-xs font-semibold transition-colors"
                                >
                                    {{ __('Tolak & Hapus') }}
                                </button>
                                <button 
                                    wire:click="approve('{{ $profile->organization_profile_id }}')"
                                    class="bg-primary hover:bg-primary-container text-white px-5 py-2.5 rounded-xl text-xs font-semibold transition-colors shadow-sm shadow-sm"
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
