<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});


Route::middleware(['auth'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::livewire('profile', 'pages::profile')->name('profile');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Modul 7: Moderasi Keahlian (Skill Tagging - Approve/Reject/Delete Tag)
    Route::livewire('skills', 'pages::admin.skills')->name('skills');

    // Dasbor Statistik Admin & Tindakan Pemblokiran Akun
    // Route::get('dashboard', \App\Livewire\Pages\Admin\Dashboard::class)->name('dashboard');

    // Modul 1: Verifikasi Akun Organisasi Mahasiswa (Ormawa)
    // Route::get('verifications', \App\Livewire\Pages\Admin\ManageVerifications::class)->name('verifications');
});

require __DIR__.'/auth.php';
