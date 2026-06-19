<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});


Route::middleware(['auth'])->group(function () {
    // Dynamic dashboard router returning 302 redirects to role-based URLs
    Route::get('dashboard', function () {
        $role = auth()->user()->role;
        if ($role === 'student') {
            return redirect()->route('student.dashboard');
        } elseif ($role === 'organization') {
            return redirect()->route('organization.dashboard');
        } elseif ($role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        abort(403);
    })->name('dashboard');

    Route::livewire('profile', 'pages::profile')->name('profile');

    // Public / Persona-neutral RESTful paths for Students (restricted to student role)
    Route::middleware('role:student')->group(function () {
        // Browse/explore vacancies
        Route::livewire('vacancies', 'pages::student.explore')->name('vacancies.index');
        Route::livewire('vacancies/{vacancy}', 'pages::student.vacancy-detail')->name('vacancies.show');

        // View progress of submitted applications
        Route::livewire('applications', 'pages::student.applications')->name('applications.index');
    });

    // View candidate portfolios (accessible by authenticated users, e.g. organizations)
    Route::livewire('students/{student}', 'pages::student.portfolio')->name('students.show');
});

// Student Route Group
Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::livewire('dashboard', 'pages::student.dashboard')->name('dashboard');
});

// Organization Route Group (Dashboard accessible to all orgs, features restricted to verified orgs)
Route::middleware(['auth', 'role:organization'])->prefix('organization')->name('organization.')->group(function () {
    Route::livewire('dashboard', 'pages::organization.dashboard')->name('dashboard');

    // Events and vacancies RESTful paths requiring verification
    Route::middleware('verified_org')->group(function () {
        // Events resource list & modification
        Route::livewire('events', 'pages::organization.events-list')->name('events.index');
        Route::livewire('events/create', 'pages::organization.create-event')->name('events.create');
        Route::livewire('events/{event}/edit', 'pages::organization.edit-event')->name('events.edit');

        // Vacancies nested under events
        Route::livewire('events/{event}/vacancies/create', 'pages::organization.create-vacancy')->name('events.vacancies.create');
        Route::livewire('vacancies/{vacancy}/edit', 'pages::organization.edit-vacancy')->name('vacancies.edit');

        // Collaborators / Event Team
        Route::livewire('events/{event}/team', 'pages::organization.team');

        // Recruitment Desk / Candidate Applications
        Route::livewire('applications', 'pages::organization.applications-index')->name('applications.index');
        Route::livewire('vacancies/{vacancy}/applications', 'pages::organization.vacancy-applications')->name('vacancies.applications');
        Route::livewire('applications/{application}', 'pages::organization.application-detail')->name('applications.show');
    });
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
