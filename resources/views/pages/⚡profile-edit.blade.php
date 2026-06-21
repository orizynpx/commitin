<?php

use App\Models\User;
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

    public $avatarFile;

    public string $student_id = '';
    public string $faculty = '';
    public string $study_program = '';
    public ?int $entry_year = null;
    public ?string $bio = '';

    public string $organization_level = 'study_program';
    public ?string $description = '';

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
            }
        }
    }

    public function updateProfile()
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
        } else {
            $rules = [
                'name' => ['required', 'string', 'max:255'],
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
        }

        session()->flash('status', 'Profil berhasil diperbarui!');
        return redirect()->route('profile');
    }
}; ?>

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
                <a href="{{ route('profile') }}" class="bg-surface-container-lowest border border-surface-dim text-on-surface-variant hover:bg-surface-container px-5 py-2.5 rounded-lg text-sm font-semibold transition-colors flex items-center">Batal</a>
                <button type="submit" class="bg-primary hover:bg-primary-container text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition-colors shadow-sm">Simpan Perubahan</button>
            </div>
        </form>
    </div>

    <div class="bg-surface-container-lowest rounded-lg shadow-sm border border-surface-dim p-8 mt-8">
        <livewire:is component="profile.⚡update-password-form" />
    </div>
</div>
