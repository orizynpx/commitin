<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});


Route::middleware(['auth'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::livewire('profile', 'pages::profile')->name('profile');
});

// Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

//     // Inline middleware sederhana untuk membatasi akses hanya untuk role 'sysadmin'
//     Route::middleware(function ($request, $next) {
//         if (auth()->user()->role !== 'admin') {
//             abort(403, 'Anda tidak memiliki hak akses ke halaman ini.');
//         }
//         return $next($request);
//     })->group(function () {

//         // Dasbor Statistik Admin & Tindakan Pemblokiran Akun
//         Route::get('/dashboard', \App\Livewire\Pages\Admin\Dashboard::class)
//             ->name('dashboard');

//         // Modul 7: Moderasi Keahlian (Skill Tagging - Approve/Reject/Delete Tag)
//         Route::get('/skills', \App\Livewire\Pages\Admin\ManageSkills::class)
//             ->name('skills');

//         // Modul 1: Verifikasi Akun Organisasi Mahasiswa (Ormawa)
//         Route::get('/verifications', \App\Livewire\Pages\Admin\ManageVerifications::class)
//             ->name('verifications');
//     });
// });

require __DIR__.'/auth.php';
