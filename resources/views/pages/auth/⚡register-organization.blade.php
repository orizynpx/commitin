<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    
    public string $organization_level = 'study_program';

    public function register(): void
    {
        $this->email = strtolower(trim($this->email));

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'organization_level' => ['required', 'string', 'in:study_program,faculty,university'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'organization',
        ]);

        $user->organizationProfile()->create([
            'organization_level' => $validated['organization_level'],
            'verification_status' => 'pending',
        ]);

        event(new Registered($user));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="flex border-b border-gray-200 mb-6">
        <a href="{{ route('register') }}" class="w-1/2 py-2 text-center font-semibold text-sm border-b-2 border-transparent text-outline hover:text-on-surface dark:hover:text-gray-300" wire:navigate>
            Akun Mahasiswa
        </a>
        <a href="{{ route('register.organization') }}" class="w-1/2 py-2 text-center font-semibold text-sm border-b-2 border-indigo-500 text-primary dark:text-indigo-400" wire:navigate>
            Akun Organisasi
        </a>
    </div>

    <form wire:submit="register">
        <div>
            <x-input-label for="name" value="Nama Organisasi" />
            <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" name="name" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" value="Email Organisasi" />
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" name="email" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="organization_level" value="Tingkat Organisasi" />
            <select wire:model="organization_level" id="organization_level" class="block mt-1 w-full rounded-md shadow-sm border-surface-dim focus:border-primary focus:ring-primary" name="organization_level" required>
                <option value="study_program">Tingkat Program Studi</option>
                <option value="faculty">Tingkat Fakultas</option>
                <option value="university">Tingkat Universitas</option>
            </select>
            <x-input-error :messages="$errors->get('organization_level')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" value="Password" />
            <x-text-input wire:model="password" id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" value="Konfirmasi Password" />
            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-on-surface-variant hover:text-on-surface rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary" href="{{ route('login') }}" wire:navigate>
                Sudah terdaftar?
            </a>

            <x-primary-button class="ms-4">
                Daftar
            </x-primary-button>
        </div>
    </form>
</div>
