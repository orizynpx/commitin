<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    public function run(): void
    {
        Skill::create([
            'skill_name' => 'Leadership',
            'status' => 'approved',
        ]);

        Skill::create([
            'skill_name' => 'Public Speaking',
            'status' => 'approved',
        ]);

        Skill::create([
            'skill_name' => 'Graphic Design',
            'status' => 'approved',
        ]);

        Skill::create([
            'skill_name' => 'Photography',
            'status' => 'approved',
        ]);

        Skill::create([
            'skill_name' => 'Videography',
            'status' => 'approved',
        ]);

        Skill::create([
            'skill_name' => 'UI/UX Design',
            'status' => 'approved',
        ]);

        Skill::create([
            'skill_name' => 'Copywriting',
            'status' => 'approved',
        ]);

        Skill::create([
            'skill_name' => 'Project Management',
            'status' => 'approved',
        ]);
    }
}
