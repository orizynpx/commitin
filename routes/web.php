<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});


Route::middleware(['auth'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::livewire('profile', 'pages::profile')->name('profile');
});

// Student Route Group
Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    // Phase 3: Discover vacancies & submit application
    // Route::livewire('explore', 'pages::student.explore')->name('explore');
    // Route::livewire('applications', 'pages::student.applications')->name('applications');
});

// Organization Route Group (Requires Admin Verification)
Route::middleware(['auth', 'verified_org'])->prefix('organization')->name('organization.')->group(function () {
    // Phase 2: Create events and publish vacancies
    // Route::livewire('events/create', 'pages::organization.create-event')->name('events.create');
    // Route::livewire('vacancies/create', 'pages::organization.create-vacancy')->name('vacancies.create');
});

// Admin Route Group
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Modul 7: Moderasi Keahlian (Skill Tagging - Approve/Reject/Delete Tag)
    Route::livewire('skills', 'pages::admin.skills')->name('skills');

    // Dasbor Statistik Admin & Tindakan Pemblokiran Akun
    Route::livewire('dashboard', 'pages::admin.dashboard')->name('dashboard');

    // Modul 1: Verifikasi Akun Organisasi Mahasiswa (Ormawa)
    Route::livewire('verifications', 'pages::admin.verifications')->name('verifications');
});

require __DIR__.'/auth.php';
