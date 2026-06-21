<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', function () {
        $role = auth()->user()->role;
        if ($role === 'student') {
            return redirect()->route('student.dashboard');
        } elseif ($role === 'organization') {
            return redirect()->route('organizer.dashboard');
        } elseif ($role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        abort(403);
    })->name('dashboard');

    Route::livewire('profile', 'pages::profile')->name('profile');

    Route::middleware('role:student')->group(function () {
        Route::livewire('vacancies', 'pages::student.explore')->name('vacancies.index');
        Route::livewire('vacancies/{vacancy}', 'pages::student.vacancy-detail')->name('vacancies.show');
        Route::livewire('applications', 'pages::student.applications')->name('applications.index');
    });

    Route::livewire('students/{student}', 'pages::student.portfolio')->name('students.show');
});

Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::livewire('dashboard', 'pages::student.dashboard')->name('dashboard');
});

Route::middleware(['auth', 'role:student,organization'])->prefix('organizer')->name('organizer.')->group(function () {
    Route::livewire('dashboard', 'pages::organization.dashboard')->name('dashboard');

    Route::middleware('verified_org')->group(function () {
        Route::livewire('events', 'pages::organization.events-list')->name('events.index');
        Route::livewire('events/create', 'pages::organization.create-event')->name('events.create');
        Route::livewire('events/{event}', 'pages::organization.event-detail')->name('events.show');
        Route::livewire('events/{event}/edit', 'pages::organization.edit-event')->name('events.edit');

        Route::livewire('events/{event}/vacancies/create', 'pages::organization.create-vacancy')->name('events.vacancies.create');
        Route::livewire('vacancies/{vacancy}/edit', 'pages::organization.edit-vacancy')->name('vacancies.edit');

        Route::livewire('events/{event}/team', 'pages::organization.event-team')->name('events.team');

        Route::livewire('applications', 'pages::organization.applications-index')->name('applications.index');
        Route::livewire('vacancies/{vacancy}/applications', 'pages::organization.vacancy-applications')->name('vacancies.applications');
        Route::livewire('applications/{application}', 'pages::organization.application-detail')->name('applications.show');
    });
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::livewire('skills', 'pages::admin.skills')->name('skills');
    Route::livewire('dashboard', 'pages::admin.dashboard')->name('dashboard');
    Route::livewire('verifications', 'pages::admin.verifications')->name('verifications');
    Route::livewire('users', 'pages::admin.users')->name('users');
});

Route::get('applications/{application}/download', function (\App\Models\VacancyApplication $application) {
    $user = auth()->user();
    $isOwner = $application->user_id === $user->user_id;
    $isOrganizer = $application->vacancy->event->organizers()->where('users.user_id', $user->user_id)->exists();
    $isAdmin = $user->role === 'admin';

    if (!$isOwner && !$isOrganizer && !$isAdmin) {
        abort(403, 'Unauthorized.');
    }

    $fileUrl = $application->file_url;
    if (empty($fileUrl)) {
        abort(404, 'File not found.');
    }

    $path = $fileUrl;
    if (\Illuminate\Support\Str::startsWith($path, '/storage/')) {
        $path = \Illuminate\Support\Str::after($path, '/storage/');
    } elseif (\Illuminate\Support\Str::startsWith($path, 'storage/')) {
        $path = \Illuminate\Support\Str::after($path, 'storage/');
    }

    if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
        return \Illuminate\Support\Facades\Storage::disk('public')->download($path);
    }

    abort(404, 'File not found.');
})->name('applications.download')->middleware(['auth']);

require __DIR__.'/auth.php';
