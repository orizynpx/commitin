<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::livewire('register/student', 'pages::auth.register')
        ->name('register');

    Route::livewire('register/organization', 'pages::auth.register-organization')
        ->name('register.organization');

    Route::livewire('login', 'pages::auth.login')
        ->name('login');
});

Route::middleware('auth')->group(function () {
    Route::livewire('verify-email', 'pages::auth.verify-email')
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::livewire('confirm-password', 'pages::auth.confirm-password')
        ->name('password.confirm');

    Route::post('logout', function (\App\Livewire\Actions\Logout $logout) {
        $logout();
        return redirect('/');
    })->name('logout');
});