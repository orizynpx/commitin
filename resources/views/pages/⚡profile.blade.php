<?php

use App\Models\User;
use App\Models\Experience;
use App\Models\Skill;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component
{
    // Common User properties
    public string $name = '';
    public string $email = '';
    public string $role = '';

    // Student Profile properties
    public string $student_id = '';
    public string $faculty = '';
    public string $study_program = '';
    public ?int $entry_year = null;
    public ?string $bio = '';

    // Organization Profile properties
    public string $organization_level = 'study_program';
    public ?string $description = '';
    public string $verification_status = 'pending';

    // Experience CRUD Form fields
    public ?string $experienceId = null;
    public string $experienceTitle = '';
    public string $experienceDescription = '';

    // Skill Form fields
    public string $skillInput = '';

    public function mount(): void
    {
        $user = Auth::user();
        $this->role = $user->role;
        $this->name = $user->name;
        $this->email = $user->email;

        if ($this->role === 'student') {
            $profile = $user->studentProfile;
            if ($profile) {
                $this->student_id = $profile->student_id;
                $this->faculty = $profile->faculty;
                $this->study_program = $profile->study_program;
                $this->entry_year = $profile->entry_year;
                $this->bio = $profile->bio;
            }
        } elseif ($this->role === 'organization') {
            $profile = $user->organizationProfile;
            if ($profile) {
                $this->organization_level = $profile->organization_level;
                $this->description = $profile->description;
                $this->verification_status = $profile->verification_status;
            }
        }
    }

    public function updateProfile(): void
    {
        $user = Auth::user();
        
        if ($this->role === 'student') {
            $validated = $this->validate([
                'name' => ['required', 'string', 'max:255'],
                'student_id' => ['required', 'string', 'max:15', 'unique:student_profile,student_id,' . ($user->studentProfile->student_profile_id ?? '') . ',student_profile_id'],
                'faculty' => ['required', 'string', 'max:100'],
                'study_program' => ['required', 'string', 'max:100'],
                'entry_year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
                'bio' => ['nullable', 'string'],
            ]);

            $user->update(['name' => $validated['name']]);
            $this->name = $user->name;

            $user->studentProfile()->updateOrCreate(
                ['user_id' => $user->user_id],
                [
                    'student_id' => $validated['student_id'],
                    'faculty' => $validated['faculty'],
                    'study_program' => $validated['study_program'],
                    'entry_year' => $validated['entry_year'],
                    'bio' => $validated['bio'],
                ]
            );
        } elseif ($this->role === 'organization') {
            $validated = $this->validate([
                'name' => ['required', 'string', 'max:255'],
                'organization_level' => ['required', 'string', 'in:study_program,faculty,university'],
                'description' => ['nullable', 'string'],
            ]);

            $user->update(['name' => $validated['name']]);
            $this->name = $user->name;

            $profile = $user->organizationProfile;
            $newStatus = ($profile && $profile->verification_status === 'rejected') ? 'pending' : ($profile->verification_status ?? 'pending');

            $user->organizationProfile()->updateOrCreate(
                ['user_id' => $user->user_id],
                [
                    'organization_level' => $validated['organization_level'],
                    'description' => $validated['description'],
                    'verification_status' => $newStatus,
                ]
            );
            $this->verification_status = $newStatus;
        }

        $this->dispatch('close-modal', 'edit-profile');
        session()->flash('status', 'Profil berhasil diperbarui!');
    }

    // Experience CRUD
    public function loadExperience(?string $id = null): void
    {
        if ($id) {
            $exp = Experience::findOrFail($id);
            if ($exp->user_id !== Auth::id()) {
                abort(403);
            }
            $this->experienceId = $exp->experience_id;
            $this->experienceTitle = $exp->title;
            $this->experienceDescription = $exp->description;
        } else {
            $this->experienceId = null;
            $this->experienceTitle = '';
            $this->experienceDescription = '';
        }
        $this->dispatch('open-modal', 'experience-modal');
    }

    public function saveExperience(): void
    {
        $validated = $this->validate([
            'experienceTitle' => ['required', 'string', 'max:100'],
            'experienceDescription' => ['required', 'string'],
        ]);

        if ($this->experienceId) {
            $exp = Experience::findOrFail($this->experienceId);
            if ($exp->user_id !== Auth::id()) {
                abort(403);
            }
            $exp->update([
                'title' => $validated['experienceTitle'],
                'description' => $validated['experienceDescription'],
            ]);
        } else {
            Auth::user()->experiences()->create([
                'title' => $validated['experienceTitle'],
                'description' => $validated['experienceDescription'],
            ]);
        }

        $this->dispatch('close-modal', 'experience-modal');
        $this->reset(['experienceId', 'experienceTitle', 'experienceDescription']);
        session()->flash('status', 'Pengalaman berhasil disimpan!');
    }

    public function deleteExperience(string $id): void
    {
        $exp = Experience::findOrFail($id);
        if ($exp->user_id !== Auth::id()) {
            abort(403);
        }
        $exp->delete();
        session()->flash('status', 'Pengalaman berhasil dihapus!');
    }

    // Skills Management
    public function addSkill(): void
    {
        $validated = $this->validate([
            'skillInput' => ['required', 'string', 'max:50'],
        ]);

        $skillName = trim($validated['skillInput']);
        if (empty($skillName)) {
            return;
        }

        // Find or create skill
        $skill = Skill::firstOrCreate(['skill_name' => $skillName]);

        $user = Auth::user();
        if (!$user->skills()->where('skills.skill_id', $skill->skill_id)->exists()) {
            $user->skills()->attach($skill->skill_id);
        }

        $this->skillInput = '';
        session()->flash('status', 'Keahlian berhasil ditambahkan!');
    }

    public function removeSkill(string $skillId): void
    {
        Auth::user()->skills()->detach($skillId);
        session()->flash('status', 'Keahlian berhasil dihapus!');
    }
}; ?>

<div>
    <!-- Status Toast Alert -->
    @if (session('status'))
        <div class="mb-4 p-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
            <span class="font-medium">{{ session('status') }}</span>
        </div>
    @endif

    <!-- Header / Hero Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
        <!-- Cover Image -->
        <div class="h-48 bg-gradient-to-r from-blue-600 to-blue-400 w-full relative">
            <div class="absolute inset-0 bg-black/10"></div>
        </div>
        
        <!-- Profile Info -->
        <div class="px-8 pb-8 relative">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-end -mt-16 mb-4 gap-4">
                <!-- Avatar & Name -->
                <div class="flex flex-col md:flex-row items-start md:items-end gap-6">
                    <div class="w-32 h-32 rounded-full border-4 border-white bg-white overflow-hidden shadow-md shrink-0 relative z-10">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($name) }}&background=f8fafc&color=2563eb&size=256" alt="Profile avatar" class="w-full h-full object-cover">
                    </div>
                    <div class="mb-2">
                        <h1 class="text-3xl font-bold text-gray-900 mb-1">{{ $name }}</h1>
                        @if ($role === 'student')
                            <p class="text-lg text-blue-600 font-medium mb-1">{{ $study_program ?: 'Belum Mengisi Jurusan' }} &bull; Angkatan {{ $entry_year ?: '-' }}</p>
                            <div class="flex items-center text-sm text-gray-500 gap-4">
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    {{ $faculty ?: 'Belum Mengisi Fakultas' }}
                                </span>
                                <span class="flex items-center gap-1 text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full font-medium">
                                    Mahasiswa Aktif
                                </span>
                            </div>
                        @elseif ($role === 'organization')
                            <p class="text-lg text-blue-600 font-medium mb-1">{{ __('Tingkat Ormawa: ') }}{{ ucfirst(str_replace('_', ' ', $organization_level)) }}</p>
                            <div class="flex items-center text-sm text-gray-500 gap-4">
                                @if ($verification_status === 'verified')
                                    <span class="flex items-center gap-1 text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full font-medium">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                        {{ __('Terverifikasi') }}
                                    </span>
                                @elseif ($verification_status === 'pending')
                                    <span class="flex items-center gap-1 text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full font-medium">
                                        {{ __('Menunggu Verifikasi') }}
                                    </span>
                                @else
                                    <span class="flex items-center gap-1 text-red-600 bg-red-50 px-2 py-0.5 rounded-full font-medium">
                                        {{ __('Verifikasi Ditolak') }}
                                    </span>
                                @endif
                            </div>
                        @elseif ($role === 'admin')
                            <p class="text-lg text-blue-600 font-medium mb-1">Administrator Platform</p>
                        @endif
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="flex gap-3 w-full md:w-auto">
                    @if ($role !== 'admin')
                        <button x-on:click="$dispatch('open-modal', 'edit-profile')" class="flex-1 md:flex-none bg-blue-600 text-white hover:bg-blue-700 px-5 py-2.5 rounded-lg text-sm font-semibold transition-colors shadow-sm shadow-blue-200 flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            Edit Profil
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Body -->
    @if ($role === 'student')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- Left/Main Column -->
            <div class="lg:col-span-2 space-y-8">
                <!-- About Section -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
                    <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        Tentang Saya
                    </h3>
                    <p class="text-gray-600 leading-relaxed text-sm whitespace-pre-line">
                        {{ $bio ?: 'Belum ada deskripsi tentang diri Anda. Silakan klik tombol Edit Profil untuk menambahkannya.' }}
                    </p>
                </div>

                <!-- Experience Timeline -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            Pengalaman Kepanitiaan & Organisasi
                        </h3>
                        <button wire:click="loadExperience()" class="text-sm font-medium text-blue-600 hover:text-blue-800">+ Tambah</button>
                    </div>
                    
                    @if (Auth::user()->experiences->isEmpty())
                        <p class="text-sm text-gray-500 italic">{{ __('Belum ada riwayat pengalaman kepanitiaan / organisasi.') }}</p>
                    @else
                        <div class="relative border-l-2 border-blue-100 ml-3 space-y-8">
                            @foreach (Auth::user()->experiences as $exp)
                                <div class="relative pl-6">
                                    <div class="absolute w-4 h-4 bg-blue-600 rounded-full -left-[9px] top-1 border-4 border-white shadow-sm"></div>
                                    <div class="flex justify-between items-start mb-1 gap-4">
                                        <h4 class="text-lg font-bold text-gray-900 leading-snug">{{ $exp->title }}</h4>
                                        <div class="flex items-center gap-2 shrink-0">
                                            <button wire:click="loadExperience('{{ $exp->experience_id }}')" class="text-xs text-blue-600 hover:text-blue-800">{{ __('Edit') }}</button>
                                            <span class="text-gray-300">|</span>
                                            <button onclick="confirm('Yakin ingin menghapus?') || event.stopImmediatePropagation()" wire:click="deleteExperience('{{ $exp->experience_id }}')" class="text-xs text-red-600 hover:text-red-800">{{ __('Hapus') }}</button>
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-600 leading-relaxed whitespace-pre-line">{{ $exp->description }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Right Sidebar Column -->
            <div class="space-y-8">
                <!-- Skills Section -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2 mb-4">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        Keahlian Saya
                    </h3>

                    <!-- Add Skill Form inline -->
                    <form wire:submit.prevent="addSkill" class="mb-4 flex gap-2">
                        <x-text-input wire:model="skillInput" placeholder="Tambah keahlian (misal: Figma)..." class="flex-1 text-sm px-3 py-1.5" />
                        <button type="submit" class="bg-blue-600 text-white hover:bg-blue-700 px-3 py-1.5 rounded-lg text-sm font-semibold transition-colors">
                            {{ __('Tambah') }}
                        </button>
                    </form>
                    
                    @if (Auth::user()->skills->isEmpty())
                        <p class="text-sm text-gray-500 italic">{{ __('Belum mengisi keahlian.') }}</p>
                    @else
                        <div class="flex flex-wrap gap-2">
                            @foreach (Auth::user()->skills as $skill)
                                <span class="bg-blue-50 border border-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-medium flex items-center gap-1.5">
                                    {{ $skill->skill_name }}
                                    <button wire:click="removeSkill('{{ $skill->skill_id }}')" class="text-blue-400 hover:text-blue-800 font-bold focus:outline-none">&times;</button>
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @elseif ($role === 'organization')
        <div class="space-y-8 mb-8">
            <!-- About Section for Organizations -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
                <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    Tentang Organisasi
                </h3>
                <p class="text-gray-600 leading-relaxed text-sm whitespace-pre-line">
                    {{ $description ?: 'Belum ada deskripsi organisasi. Silakan klik tombol Edit Profil untuk menambahkannya.' }}
                </p>
            </div>
        </div>
    @elseif ($role === 'admin')
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 mb-8">
            <h3 class="text-xl font-bold text-gray-900 mb-2">Informasi Administrator</h3>
            <p class="text-gray-600 text-sm">Nama Akun: {{ $name }}</p>
            <p class="text-gray-600 text-sm">Email: {{ $email }}</p>
            <p class="text-gray-500 text-xs mt-4">Anda masuk dengan peran Admin. Anda dapat menavigasi menu di bilah samping untuk melakukan verifikasi akun ormawa.</p>
        </div>
    @endif

    <!-- EDIT PROFILE MODAL -->
    <x-modal name="edit-profile" :show="$errors->isNotEmpty() && $errors->hasAny(['name', 'student_id', 'faculty', 'study_program', 'entry_year', 'bio', 'organization_level', 'description'])">
        <form wire:submit.prevent="updateProfile" class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Sunting Profil') }}
            </h2>

            <div class="mt-4">
                <x-input-label for="edit_name" :value="__('Nama Lengkap')" />
                <x-text-input wire:model="name" id="edit_name" class="block mt-1 w-full" type="text" required />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            @if ($role === 'student')
                <div class="mt-4">
                    <x-input-label for="edit_student_id" :value="__('NIM / Student ID')" />
                    <x-text-input wire:model="student_id" id="edit_student_id" class="block mt-1 w-full" type="text" required />
                    <x-input-error :messages="$errors->get('student_id')" class="mt-2" />
                </div>

                <div class="mt-4">
                    <x-input-label for="edit_faculty" :value="__('Fakultas')" />
                    <x-text-input wire:model="faculty" id="edit_faculty" class="block mt-1 w-full" type="text" required />
                    <x-input-error :messages="$errors->get('faculty')" class="mt-2" />
                </div>

                <div class="mt-4">
                    <x-input-label for="edit_study_program" :value="__('Program Studi / Jurusan')" />
                    <x-text-input wire:model="study_program" id="edit_study_program" class="block mt-1 w-full" type="text" required />
                    <x-input-error :messages="$errors->get('study_program')" class="mt-2" />
                </div>

                <div class="mt-4">
                    <x-input-label for="edit_entry_year" :value="__('Tahun Masuk / Angkatan')" />
                    <x-text-input wire:model="entry_year" id="edit_entry_year" class="block mt-1 w-full" type="number" min="1900" max="2100" />
                    <x-input-error :messages="$errors->get('entry_year')" class="mt-2" />
                </div>

                <div class="mt-4">
                    <x-input-label for="edit_bio" :value="__('Tentang Saya / Bio')" />
                    <textarea wire:model="bio" id="edit_bio" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 text-sm" rows="4"></textarea>
                    <x-input-error :messages="$errors->get('bio')" class="mt-2" />
                </div>
            @elseif ($role === 'organization')
                <div class="mt-4">
                    <x-input-label for="edit_org_level" :value="__('Tingkat Organisasi')" />
                    <select wire:model="organization_level" id="edit_org_level" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600" required>
                        <option value="study_program">{{ __('Tingkat Program Studi') }}</option>
                        <option value="faculty">{{ __('Tingkat Fakultas') }}</option>
                        <option value="university">{{ __('Tingkat Universitas') }}</option>
                    </select>
                    <x-input-error :messages="$errors->get('organization_level')" class="mt-2" />
                </div>

                <div class="mt-4">
                    <x-input-label for="edit_description" :value="__('Deskripsi Organisasi')" />
                    <textarea wire:model="description" id="edit_description" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 text-sm" rows="4"></textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                </div>
            @endif

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close-modal', 'edit-profile')">
                    {{ __('Batal') }}
                </x-secondary-button>

                <x-primary-button>
                    {{ __('Simpan') }}
                </x-primary-button>
            </div>
        </form>
    </x-modal>

    <!-- EXPERIENCE MODAL -->
    <x-modal name="experience-modal">
        <form wire:submit.prevent="saveExperience" class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ $experienceId ? __('Edit Pengalaman') : __('Tambah Pengalaman Baru') }}
            </h2>

            <div class="mt-4">
                <x-input-label for="exp_title" :value="__('Judul Pengalaman / Posisi')" />
                <x-text-input wire:model="experienceTitle" id="exp_title" placeholder="Misal: Staff Pubdok" class="block mt-1 w-full" type="text" required />
                <x-input-error :messages="$errors->get('experienceTitle')" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-input-label for="exp_desc" :value="__('Deskripsi / Penjelasan Singkat')" />
                <textarea wire:model="experienceDescription" id="exp_desc" placeholder="Tuliskan kontribusi dan program kerja yang Anda tangani..." class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 text-sm" rows="4" required></textarea>
                <x-input-error :messages="$errors->get('experienceDescription')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close-modal', 'experience-modal')">
                    {{ __('Batal') }}
                </x-secondary-button>

                <x-primary-button>
                    {{ __('Simpan') }}
                </x-primary-button>
            </div>
        </form>
    </x-modal>
</div>
