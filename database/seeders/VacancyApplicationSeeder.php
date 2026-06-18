<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vacancy;
use App\Models\VacancyApplication;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VacancyApplicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bumawati = User::query()->where('email', 'buma@example.com')->first();
        $andi = User::query()->where('email', 'Andi@example.com')->first();

        $acara = Vacancy::query()->where('division', 'Acara')->first();
        $publikasi = Vacancy::query()->where('division', 'Publikasi')->first();

        VacancyApplication::create([
            'user_id' => $bumawati->user_id,
            'vacancy_id' => $acara->vacancy_id,
            'status' => 'accepted',
            'file_url' => 'https://example.com/cv-bumawati.pdf',
        ]);

        VacancyApplication::create([
            'user_id' => $andi->user_id,
            'vacancy_id' => $publikasi->vacancy_id,
            'status' => 'pending',
            'file_url' => 'https://example.com/cv-andi.pdf',
        ]);

        VacancyApplication::create([
            'user_id' => $andi->user_id,
            'vacancy_id' => $acara->vacancy_id,
            'status' => 'rejected',
            'file_url' => 'https://example.com/cv-andi-v2.pdf',
        ]);
    }
}
