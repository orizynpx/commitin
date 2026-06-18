<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Skill::create([
            'skill_name' => 'Leadership',
        ]);

        Skill::create([
            'skill_name' => 'Public Speaking',
        ]);

        Skill::create([
            'skill_name' => 'Graphic Design',
        ]);

        Skill::create([
            'skill_name' => 'Photography',
        ]);

        Skill::create([
            'skill_name' => 'Videography',
        ]);

        Skill::create([
            'skill_name' => 'UI/UX Design',
        ]);

        Skill::create([
            'skill_name' => 'Copywriting',
        ]);

        Skill::create([
            'skill_name' => 'Project Management',
        ]);
    }
}
