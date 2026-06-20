<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});


Route::middleware(['auth'])->group(function () {
    // Dynamic dashboard router returning 302 redirects to role-based URLs
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

// Organizer Route Group (Dashboard accessible to all orgs/students, features restricted to verified orgs)
Route::middleware(['auth', 'role:student,organization'])->prefix('organizer')->name('organizer.')->group(function () {
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
        Route::livewire('events/{event}/team', 'pages::organization.event-team')->name('events.team');

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

Route::get('applications/{application}/download', function (\App\Models\VacancyApplication $application) {
    $user = auth()->user();
    $isOwner = $application->user_id === $user->user_id;
    $isOrganizer = $application->vacancy->event->organizers()->where('users.user_id', $user->user_id)->exists();
    $isAdmin = $user->role === 'admin';

    if (!$isOwner && !$isOrganizer && !$isAdmin) {
        abort(403, 'Unauthorized.');
    }

    $fileUrl = $application->file_url;

    if (\Illuminate\Support\Str::startsWith($fileUrl, ['http://', 'https://']) && \Illuminate\Support\Str::contains($fileUrl, 'example.com')) {
        $dummyPdf = "%PDF-1.4\n" .
            "1 0 obj <</Type /Catalog /Pages 2 0 R>> endobj\n" .
            "2 0 obj <</Type /Pages /Kids [3 0 R] /Count 1>> endobj\n" .
            "3 0 obj <</Type /Page /Parent 2 0 R /Resources <<>> /MediaBox [0 0 595 842] /Contents 4 0 R>> endobj\n" .
            "4 0 obj <</Length 46>> stream\n" .
            "BT /F1 24 Tf 100 700 Td (Mock CV Placeholder PDF) Tj ET\n" .
            "endstream\n" .
            "endobj\n" .
            "xref\n" .
            "0 5\n" .
            "0000000000 65535 f\n" .
            "0000000009 00000 n\n" .
            "0000000056 00000 n\n" .
            "0000000111 00000 n\n" .
            "0000000212 00000 n\n" .
            "trailer <</Size 5 /Root 1 0 R>>\n" .
            "startxref\n" .
            "306\n" .
            "%%EOF";

        return response($dummyPdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . basename($fileUrl) . '"');
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

