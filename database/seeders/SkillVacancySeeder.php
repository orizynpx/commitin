<?php

namespace Database\Seeders;

use App\Models\Skill;
use App\Models\Vacancy;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SkillVacancySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $acara = Vacancy::query()->where('division', 'Acara')->first();
        $publikasi = Vacancy::query()->where('division', 'Publikasi')->first();

        $leadership = Skill::query()->where('skill_name', 'Leadership')->first();
        $publicSpeaking = Skill::query()->where('skill_name', 'Public Speaking')->first();
        $graphicDesign = Skill::query()->where('skill_name', 'Graphic Design')->first();

        DB::table('skill_vacancy')->insert([
            [
                'skill_id' => $leadership->skill_id,
                'vacancy_id' => $acara->vacancy_id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'skill_id' => $publicSpeaking->skill_id,
                'vacancy_id' => $acara->vacancy_id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'skill_id' => $graphicDesign->skill_id,
                'vacancy_id' => $publikasi->vacancy_id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
