<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vacancy;
use App\Models\VacancyApplication;
use Illuminate\Database\Seeder;

class VacancyApplicationSeeder extends Seeder
{
    public function run(): void
    {
        $bumawati = User::query()->where('email', 'buma@example.com')->first();
        $andi = User::query()->where('email', 'andi@example.com')->first();

        $acara = Vacancy::query()->where('division', 'Acara')->first();
        $publikasi = Vacancy::query()->where('division', 'Publikasi')->first();

        $appBuma = VacancyApplication::create([
            'user_id' => $bumawati->user_id,
            'vacancy_id' => $acara->vacancy_id,
            'status' => 'accepted',
            'file_url' => '/documents/cv_bumawati.pdf',
        ]);

        $appBuma->attachments()->create([
            'file_url' => '/documents/cv_bumawati.pdf',
            'file_name' => 'cv_bumawati.pdf',
        ]);

        $appBuma->attachments()->create([
            'file_url' => '/documents/portofolio_buma.docx',
            'file_name' => 'portofolio_buma.docx',
        ]);

        $appAndi1 = VacancyApplication::create([
            'user_id' => $andi->user_id,
            'vacancy_id' => $publikasi->vacancy_id,
            'status' => 'pending',
            'file_url' => '/documents/andi_cv.pdf',
        ]);

        $appAndi1->attachments()->create([
            'file_url' => '/documents/andi_cv.pdf',
            'file_name' => 'andi_cv.pdf',
        ]);

        $appAndi2 = VacancyApplication::create([
            'user_id' => $andi->user_id,
            'vacancy_id' => $acara->vacancy_id,
            'status' => 'rejected',
            'file_url' => '/documents/andi_cv.pdf',
        ]);

        $appAndi2->attachments()->create([
            'file_url' => '/documents/andi_cv.pdf',
            'file_name' => 'andi_cv.pdf',
        ]);
    }
}
