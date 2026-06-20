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
    
    // Student Profile fields
    public string $student_id = '';
    public string $faculty = '';
    public string $study_program = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'student_id' => ['required', 'string', 'max:15', 'unique:student_profile,student_id'],
            'faculty' => ['required', 'string', 'max:100'],
            'study_program' => ['required', 'string', 'max:100'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        // Create user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'student',
        ]);

        // Create student profile
        $user->studentProfile()->create([
            'student_id' => $validated['student_id'],
            'faculty' => $validated['faculty'],
            'study_program' => $validated['study_program'],
        ]);

        event(new Registered($user));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <!-- Registration Type Tabs -->
    <div class="flex border-b border-gray-200  mb-6">
        <a href="{{ route('register') }}" class="w-1/2 py-2 text-center font-semibold text-sm border-b-2 border-indigo-500 text-primary dark:text-indigo-400" wire:navigate>
            {{ __('Student Account') }}
        </a>
        <a href="{{ route('register.organization') }}" class="w-1/2 py-2 text-center font-semibold text-sm border-b-2 border-transparent text-outline hover:text-on-surface  dark:hover:text-gray-300" wire:navigate>
            {{ __('Organization Account') }}
        </a>
    </div>

    <form wire:submit="register">
        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" name="name" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" name="email" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Student ID -->
        <div class="mt-4">
            <x-input-label for="student_id" :value="__('Student ID / NIM')" />
            <x-text-input wire:model="student_id" id="student_id" class="block mt-1 w-full" type="text" name="student_id" required />
            <x-input-error :messages="$errors->get('student_id')" class="mt-2" />
        </div>

        <!-- Faculty -->
        <div class="mt-4">
            <x-input-label for="faculty" :value="__('Faculty')" />
            <x-text-input wire:model="faculty" id="faculty" class="block mt-1 w-full" type="text" name="faculty" required />
            <x-input-error :messages="$errors->get('faculty')" class="mt-2" />
        </div>

        <!-- Study Program -->
        <div class="mt-4">
            <x-input-label for="study_program" :value="__('Study Program')" />
            <x-text-input wire:model="study_program" id="study_program" class="block mt-1 w-full" type="text" name="study_program" required />
            <x-input-error :messages="$errors->get('study_program')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input wire:model="password" id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-on-surface-variant  hover:text-on-surface  rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary " href="{{ route('login') }}" wire:navigate>
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</div>
