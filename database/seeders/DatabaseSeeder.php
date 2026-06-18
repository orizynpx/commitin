<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
        UserSeeder::class,
        SkillSeeder::class,

        StudentProfileSeeder::class,
        OrganizationProfileSeeder::class,

        ExperienceSeeder::class,

        EventSeeder::class,
        VacancySeeder::class,

        EventOrganizerSeeder::class,
        SkillUserSeeder::class,
        SkillVacancySeeder::class,

        VacancyApplicationSeeder::class,
    ]);
    }
}
