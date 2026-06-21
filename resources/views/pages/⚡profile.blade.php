<?php

use App\Models\User;
use App\Models\Experience;
use App\Models\Skill;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.app')] class extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public string $role = '';
    public bool $isEditing = false;

    public $avatarFile;

    public string $student_id = '';
    public string $faculty = '';
    public string $study_program = '';
    public ?int $entry_year = null;
    public ?string $bio = '';

    public string $organization_level = 'study_program';
    public ?string $description = '';
    public string $verification_status = 'pending';

    public ?string $experienceId = null;
    public string $experienceTitle = '';
    public string $experienceDescription = '';

    public array $selectedSkills = [];

    public function mount(): void
    {
        $user = Auth::user();
        if ($user->role === 'admin') {
            abort(403, 'Administrator tidak memiliki halaman profil.');
        }

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
            $this->selectedSkills = $user->skills->pluck('skill_id')->toArray();
        } elseif ($this->role === 'organization') {
            $profile = $user->organizationProfile;
            if ($profile) {
                $this->organization_level = $profile->organization_level;
                $this->description = $profile->description;
                $this->verification_status = $profile->verification_status;
            }
        }
    }

    public function render()
    {
        return view('pages.⚡profile');
    }

    public function updatedSelectedSkills($value): void
    {
        Auth::user()->skills()->sync($value);
        session()->flash('status', 'Keahlian berhasil diperbarui!');
    }

    public function updateProfile(): void
    {
        $user = Auth::user();
        
        if ($this->role === 'student') {
            $rules = [
                'name' => ['required', 'string', 'max:255'],
                'student_id' => ['required', 'string', 'max:15', 'unique:student_profile,student_id,' . ($user->studentProfile->student_profile_id ?? '') . ',student_profile_id'],
                'faculty' => ['required', 'string', 'max:100'],
                'study_program' => ['required', 'string', 'max:100'],
                'entry_year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
                'bio' => ['nullable', 'string'],
            ];

            if ($this->avatarFile) {
                $rules['avatarFile'] = ['image', 'max:5120'];
            }

            $validated = $this->validate($rules);

            $updateData = ['name' => $validated['name']];
            if ($this->avatarFile) {
                $path = $this->avatarFile->store('avatars', 'public');
                $updateData['avatar_url'] = Storage::url($path);
            }
            $user->update($updateData);
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
            $rules = [
                'name' => ['required', 'string', 'max:255'],
                'organization_level' => ['required', 'string', 'in:study_program,faculty,university'],
                'description' => ['nullable', 'string'],
            ];

            if ($this->avatarFile) {
                $rules['avatarFile'] = ['image', 'max:5120'];
            }

            $validated = $this->validate($rules);

            $updateData = ['name' => $validated['name']];
            if ($this->avatarFile) {
                $path = $this->avatarFile->store('avatars', 'public');
                $updateData['avatar_url'] = Storage::url($path);
            }
            $user->update($updateData);
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

        $this->isEditing = false;
        $this->avatarFile = null;
        session()->flash('status', 'Profil berhasil diperbarui!');
    }

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
}
?>

<div>
    @if (session('status'))
        <div class="mb-4 p-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
            <span class="font-medium">{{ session('status') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 p-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    @if ($isEditing)
        <div class="max-w-2xl mx-auto py-8">
            <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-surface-dim p-6">
                <h2 class="text-2xl font-bold text-on-surface mb-6">Sunting Profil</h2>
                <form wire:submit.prevent="updateProfile" class="space-y-6">
                    <div>
                        <label class="block text-sm font-semibold text-on-surface-variant mb-2">Nama Lengkap:</label>
                        <input type="text" wire:model="name" required class="w-full border border-surface-dim rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary" />
                        @error('name') <span class="text-xs text-error block mt-1">{{ $message }}</span> @enderror
                    </div>

                    @if ($role === 'student')
                        <div>
                            <label class="block text-sm font-semibold text-on-surface-variant mb-2">NIM / Student ID:</label>
                            <input type="text" wire:model="student_id" required class="w-full border border-surface-dim rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary" />
                            @error('student_id') <span class="text-xs text-error block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-on-surface-variant mb-2">Fakultas:</label>
                            <input type="text" wire:model="faculty" required class="w-full border border-surface-dim rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary" />
                            @error('faculty') <span class="text-xs text-error block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-on-surface-variant mb-2">Program Studi / Jurusan:</label>
                            <input type="text" wire:model="study_program" required class="w-full border border-surface-dim rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary" />
                            @error('study_program') <span class="text-xs text-error block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-on-surface-variant mb-2">Tahun Masuk / Angkatan:</label>
                            <input type="number" wire:model="entry_year" min="1900" max="2100" class="w-full border border-surface-dim rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary" />
                            @error('entry_year') <span class="text-xs text-error block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-on-surface-variant mb-2">Tentang Saya / Bio:</label>
                            <textarea wire:model="bio" rows="4" class="w-full border border-surface-dim rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
                            @error('bio') <span class="text-xs text-error block mt-1">{{ $message }}</span> @enderror
                        </div>
                    @elseif ($role === 'organization')
                        <div>
                            <label class="block text-sm font-semibold text-on-surface-variant mb-2">Tingkat Organisasi:</label>
                            <select wire:model="organization_level" required class="w-full border border-surface-dim rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                                <option value="study_program">Tingkat Program Studi</option>
                                <option value="faculty">Tingkat Fakultas</option>
                                <option value="university">Tingkat Universitas</option>
                            </select>
                            @error('organization_level') <span class="text-xs text-error block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-on-surface-variant mb-2">Deskripsi Organisasi:</label>
                            <textarea wire:model="description" rows="4" class="w-full border border-surface-dim rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
                            @error('description') <span class="text-xs text-error block mt-1">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    <div>
                        <label class="block text-sm font-semibold text-on-surface-variant mb-2">Foto Profil / Avatar:</label>
                        <input type="file" wire:model="avatarFile" accept="image/*" class="w-full text-sm text-on-surface-variant file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-surface-container file:text-primary hover:file:bg-surface-dim transition-colors" />
                        <div wire:loading wire:target="avatarFile" class="text-xs text-primary mt-2">
                            Sedang mengunggah foto...
                        </div>
                        @error('avatarFile') <span class="text-xs text-error block mt-1">{{ $message }}</span> @enderror
                        
                        @if ($avatarFile && method_exists($avatarFile, 'getMimeType') && str_starts_with($avatarFile->getMimeType(), 'image/'))
                            <div class="mt-4">
                                <p class="text-xs text-on-surface-variant mb-2">Pratinjau Foto Baru:</p>
                                <img src="{{ $avatarFile->temporaryUrl() }}" class="w-24 h-24 object-cover rounded-full border-4 border-surface-container" />
                            </div>
                        @elseif (Auth::user()->avatar_url)
                            <div class="mt-4">
                                <p class="text-xs text-outline-variant mb-2">Foto Saat Ini:</p>
                                <img src="{{ Auth::user()->avatar_url }}" class="w-24 h-24 object-cover rounded-full border-4 border-surface-container" />
                            </div>
                        @endif
                    </div>

                    <div class="pt-4 border-t border-surface-dim flex justify-end gap-3">
                        <button type="button" wire:click="$set('isEditing', false)" class="bg-surface-container-lowest border border-surface-dim text-on-surface-variant hover:bg-surface-container px-5 py-2.5 rounded-lg text-sm font-semibold transition-colors">Batal</button>
                        <button type="submit" class="bg-primary hover:bg-primary-container text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition-colors shadow-sm">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    @else
        <div class="bg-surface-container-lowest rounded-lg shadow-sm border border-surface-dim overflow-hidden mb-8">
            <div class="h-48 bg-primary w-full relative">
                <div class="absolute inset-0 bg-black/10"></div>
            </div>
            
            <div class="px-8 pb-8 relative">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-end -mt-16 mb-4 gap-4">
                    <div class="flex flex-col md:flex-row items-start md:items-end gap-6">
                        <div class="w-32 h-32 rounded-full border-4 border-white bg-surface-container-lowest overflow-hidden shadow-md shrink-0 relative z-10">
                            @if(Auth::user()->avatar_url)
                                <img src="{{ Auth::user()->avatar_url }}" alt="Profile avatar" class="w-full h-full object-cover">
                            @else
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($name) }}&background=f8fafc&color=2563eb&size=256" alt="Profile avatar" class="w-full h-full object-cover">
                            @endif
                        </div>
                        <div class="mb-2">
                            <h1 class="text-3xl font-bold text-on-surface mb-1">{{ $name }}</h1>
                            @if ($role === 'student')
                                <p class="text-lg text-primary font-medium mb-1">{{ $study_program ?: 'Belum Mengisi Jurusan' }} &bull; Angkatan {{ $entry_year ?: '-' }}</p>
                                <div class="flex items-center text-sm text-outline gap-4">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                        {{ $faculty ?: 'Belum Mengisi Fakultas' }}
                                    </span>
                                    <span class="flex items-center gap-1 text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full font-medium">
                                        Mahasiswa Aktif
                                    </span>
                                </div>
                            @elseif ($role === 'organization')
                                <p class="text-lg text-primary font-medium mb-1">{{ __('Tingkat Ormawa: ') }} {{ $organization_level === 'study_program' ? 'Program Studi' : ($organization_level === 'faculty' ? 'Fakultas' : ($organization_level === 'university' ? 'Universitas' : str_replace('_', ' ', $organization_level))) }}</p>
                                <div class="flex items-center text-sm text-outline gap-4">
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
                            @endif
                        </div>
                    </div>
                    
                    <div class="flex gap-3 w-full md:w-auto">
                        @if ($role !== 'admin')
                            <button wire:click="$set('isEditing', true)" class="flex-1 md:flex-none bg-primary text-white hover:bg-primary-container px-5 py-2.5 rounded-lg text-sm font-semibold transition-colors shadow-sm shadow-primary-fixed-dim flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                Edit Profil
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if ($role === 'student')
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                <div class="lg:col-span-2 space-y-8">
                    <div class="bg-surface-container-lowest rounded-lg shadow-sm border border-surface-dim p-8">
                        <h3 class="text-xl font-bold text-on-surface mb-4 flex items-center gap-2">
                            <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            Tentang Saya
                        </h3>
                        <p class="text-on-surface-variant leading-relaxed text-sm whitespace-pre-line">
                            {{ $bio ?: 'Belum ada deskripsi tentang diri Anda. Silakan klik tombol Edit Profil untuk menambahkannya.' }}
                        </p>
                    </div>

                    <div class="bg-surface-container-lowest rounded-lg shadow-sm border border-surface-dim p-8">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-bold text-on-surface flex items-center gap-2">
                                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                Pengalaman Kepanitiaan & Organisasi
                            </h3>
                            <button wire:click="loadExperience()" class="text-sm font-medium text-primary hover:text-on-primary-container">+ Tambah</button>
                        </div>
                        
                        @if (Auth::user()->experiences->isEmpty())
                            <p class="text-sm text-outline italic">{{ __('Belum ada riwayat pengalaman kepanitiaan / organisasi.') }}</p>
                        @else
                            <div class="relative border-l-2 border-primary-fixed-dim ml-3 space-y-8">
                                @foreach (Auth::user()->experiences as $exp)
                                    <div class="relative pl-6">
                                        <div class="absolute w-4 h-4 bg-primary rounded-full -left-[9px] top-1 border-4 border-white shadow-sm"></div>
                                        <div class="flex justify-between items-start mb-1 gap-4">
                                            <h4 class="text-lg font-bold text-on-surface leading-snug">{{ $exp->title }}</h4>
                                            <div class="flex items-center gap-2 shrink-0">
                                                <button wire:click="loadExperience('{{ $exp->experience_id }}')" class="text-xs text-primary hover:text-on-primary-container">{{ __('Edit') }}</button>
                                                <span class="text-outline-variant">|</span>
                                                <button onclick="confirm('Yakin ingin menghapus?') || event.stopImmediatePropagation()" wire:click="deleteExperience('{{ $exp->experience_id }}')" class="text-xs text-red-600 hover:text-red-800">{{ __('Hapus') }}</button>
                                            </div>
                                        </div>
                                        <p class="text-sm text-on-surface-variant leading-relaxed whitespace-pre-line">{{ $exp->description }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
                
                <div class="space-y-8">
                    <div class="bg-surface-container-lowest rounded-lg shadow-sm border border-surface-dim p-6">
                        <h3 class="text-lg font-bold text-on-surface flex items-center gap-2 mb-4">
                            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            Keahlian Saya
                        </h3>
                        <livewire:is component="⚡skill-selector" wire:model="selectedSkills" />
                    </div>
                </div>
            </div>
        @elseif ($role === 'organization')
            <div class="space-y-8 mb-8">
                <div class="bg-surface-container-lowest rounded-lg shadow-sm border border-surface-dim p-8">
                    <h3 class="text-xl font-bold text-on-surface mb-4 flex items-center gap-2">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        Tentang Organisasi
                    </h3>
                    <p class="text-on-surface-variant leading-relaxed text-sm whitespace-pre-line">
                        {{ $description ?: 'Belum ada deskripsi organisasi. Silakan klik tombol Edit Profil untuk menambahkannya.' }}
                    </p>
                </div>
            </div>
        @endif

        @if ($role !== 'admin')
            <div class="bg-surface-container-lowest rounded-lg shadow-sm border border-surface-dim p-8 mb-8">
                <livewire:is component="profile.⚡update-password-form" />
            </div>
        @endif

        <x-modal name="experience-modal">
            <form wire:submit.prevent="saveExperience" class="p-6">
                <h2 class="text-lg font-medium text-on-surface dark:text-gray-100">
                    {{ $experienceId ? __('Edit Pengalaman') : __('Tambah Pengalaman Baru') }}
                </h2>

                <div class="mt-4">
                    <x-input-label for="exp_title" :value="__('Judul Pengalaman / Posisi')" />
                    <x-text-input wire:model="experienceTitle" id="exp_title" placeholder="Misal: Staff Pubdok" class="block mt-1 w-full" type="text" required />
                    <x-input-error :messages="$errors->get('experienceTitle')" class="mt-2" />
                </div>

                <div class="mt-4">
                    <x-input-label for="exp_desc" :value="__('Deskripsi / Penjelasan Singkat')" />
                    <textarea wire:model="experienceDescription" id="exp_desc" placeholder="Tuliskan kontribusi dan program kerja yang Anda tangani..." class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-outline-variant focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 text-sm" rows="4" required></textarea>
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
    @endif
</div>
