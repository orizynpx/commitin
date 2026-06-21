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

        Skill::create([
            'skill_name' => 'Golang',
            'status' => 'pending',
        ]);

        Skill::create([
            'skill_name' => 'Svelte',
            'status' => 'pending',
        ]);

        Skill::create([
            'skill_name' => 'Docker',
            'status' => 'pending',
        ]);

        Skill::create([
            'skill_name' => 'Public Relations',
            'status' => 'pending',
        ]);

        Skill::create([
            'skill_name' => 'Spamming',
            'status' => 'rejected',
        ]);

        Skill::create([
            'skill_name' => 'Gaming Core',
            'status' => 'rejected',
        ]);
    }
}
