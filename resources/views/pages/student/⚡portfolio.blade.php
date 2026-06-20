<?php

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\User;

new #[Layout('layouts.app')] class extends Component
{
    public User $user;
    public bool $isOwnProfile = false;

    public function mount(string $student): void
    {
        $user = User::with([
            'studentProfile',
            'experiences',
            'skills' => function ($q) {
                $q->where('skills.status', 'approved');
            }
        ])
        ->where(function ($query) use ($student) {
            $query->where('user_id', $student)
                  ->orWhereHas('studentProfile', function ($sub) use ($student) {
                      $sub->where('student_id', $student);
                  });
        })
        ->first();

        if (!$user || $user->role !== 'student') {
            abort(404, 'Mahasiswa tidak ditemukan.');
        }

        $this->user = $user;
        $this->isOwnProfile = (auth()->id() === $user->user_id);
    }
}; ?>

<div class="max-w-5xl mx-auto py-8 px-4">
    <!-- Header/Profile Card Section -->
    <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-surface-dim overflow-hidden mb-8">
        <!-- Cover Photo (Modern Gradient) -->
        <div class="h-48 w-full     relative"></div>

        <!-- Profile Details Container -->
        <div class="px-6 pb-6 relative flex flex-col sm:flex-row sm:items-end justify-between -mt-16 gap-6">
            <div class="flex flex-col sm:flex-row items-center sm:items-end gap-4 text-center sm:text-left">
                <!-- Avatar overlapping the cover photo -->
                @if($user->avatar_url)
                    <img 
                        src="{{ $user->avatar_url }}" 
                        alt="{{ $user->name }}" 
                        class="w-32 h-32 rounded-full border-4 border-white shadow-md bg-surface-container-lowest object-cover"
                    />
                @else
                    <img 
                        src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=3b82f6&color=fff&size=128&bold=true" 
                        alt="{{ $user->name }}" 
                        class="w-32 h-32 rounded-full border-4 border-white shadow-md bg-surface-container-lowest object-cover"
                    />
                @endif
                
                <div class="mb-2">
                    <div class="flex items-center justify-center sm:justify-start gap-2 flex-wrap">
                        <h1 class="text-2xl font-bold text-on-surface">{{ $user->name }}</h1>
                        <span class="bg-surface-container text-primary text-xs font-semibold px-2.5 py-0.5 rounded-full border border-primary-fixed-dim uppercase">
                            {{ __('Mahasiswa') }}
                        </span>
                    </div>
                    <p class="text-sm text-outline-variant font-medium">
                        {{ $user->studentProfile->study_program ?? '-' }} &bull; {{ $user->studentProfile->faculty ?? '-' }}
                    </p>
                    <p class="text-xs text-slate-400 mt-1">
                        {{ $user->email }}
                    </p>
                </div>
            </div>

            <!-- Action buttons (Only show if own profile) -->
            @if($isOwnProfile)
                <div class="flex justify-center sm:justify-end gap-3 self-center sm:self-end">
                    <a 
                        href="{{ route('profile') }}" 
                        class="inline-flex items-center gap-2 bg-primary hover:bg-primary-container text-white font-semibold px-4 py-2.5 rounded-xl text-sm transition-colors shadow-sm shadow-blue-100"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                        </svg>
                        Edit Profil
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Details Grid Layout -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Main/Left Column -->
        <div class="md:col-span-2 space-y-8">
            <!-- Tentang Saya (Bio) Section -->
            <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-surface-dim p-6">
                <h2 class="text-lg font-bold text-on-surface mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Tentang Saya
                </h2>
                <div class="text-sm text-on-surface-variant leading-relaxed whitespace-pre-line">
                    {{ $user->studentProfile->bio ?? 'Belum ada biodata singkat yang ditulis.' }}
                </div>
            </div>

            <!-- Pengalaman Kepanitiaan & Organisasi Timeline Section -->
            <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-surface-dim p-6">
                <h2 class="text-lg font-bold text-on-surface mb-6 flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    Pengalaman Kepanitiaan & Organisasi
                </h2>

                <div class="relative border-l border-surface-dim ml-4 space-y-6">
                    @forelse($user->experiences as $experience)
                        <div class="relative pl-6">
                            <!-- Bullet node -->
                            <div class="absolute -left-1.5 top-1.5 w-3 h-3 rounded-full bg-blue-500 border border-white"></div>
                            
                            <div>
                                <h3 class="text-sm font-bold text-slate-950">{{ $experience->title }}</h3>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    Diperbarui {{ $experience->updated_at->format('d M Y') }}
                                </p>
                                <div class="text-xs text-on-surface-variant mt-2 leading-relaxed whitespace-pre-line">
                                    {{ $experience->description }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="pl-6 text-sm text-slate-400 italic">
                            Belum ada riwayat pengalaman kepanitiaan/organisasi yang ditambahkan.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sidebar/Right Column -->
        <div class="space-y-8">
            <!-- Keahlian Terverifikasi Section -->
            <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-surface-dim p-6">
                <h2 class="text-md font-bold text-on-surface mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                    </svg>
                    Keahlian Terverifikasi
                </h2>
                
                <div class="flex flex-wrap gap-2">
                    @forelse($user->skills as $skill)
                        <span class="text-xs bg-surface-container text-on-surface-variant border border-surface-dim/60 px-3 py-1.5 rounded-lg font-medium shadow-sm flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                            {{ $skill->skill_name }}
                        </span>
                    @empty
                        <span class="text-xs text-slate-400 italic">Belum ada keahlian terverifikasi.</span>
                    @endforelse
                </div>
            </div>

            <!-- Informasi Akademik Section -->
            <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-surface-dim p-6">
                <h2 class="text-md font-bold text-on-surface mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                    </svg>
                    Informasi Akademik
                </h2>

                <div class="divide-y divide-surface-dim text-sm">
                    <div class="py-3 flex justify-between">
                        <span class="text-slate-400 font-medium">NIM</span>
                        <span class="font-bold text-on-surface">{{ $user->studentProfile->student_id ?? '-' }}</span>
                    </div>
                    <div class="py-3 flex justify-between">
                        <span class="text-slate-400 font-medium">Program Studi</span>
                        <span class="font-bold text-on-surface text-right max-w-[160px] truncate" title="{{ $user->studentProfile->study_program ?? '-' }}">
                            {{ $user->studentProfile->study_program ?? '-' }}
                        </span>
                    </div>
                    <div class="py-3 flex justify-between">
                        <span class="text-slate-400 font-medium">Fakultas</span>
                        <span class="font-bold text-on-surface text-right max-w-[160px] truncate" title="{{ $user->studentProfile->faculty ?? '-' }}">
                            {{ $user->studentProfile->faculty ?? '-' }}
                        </span>
                    </div>
                    <div class="py-3 flex justify-between">
                        <span class="text-slate-400 font-medium">Angkatan</span>
                        <span class="font-bold text-on-surface">{{ $user->studentProfile->entry_year ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
