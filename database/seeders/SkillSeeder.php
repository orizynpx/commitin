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
        Skill::insert([
            ['skill_name' => 'Leadership'],
            ['skill_name' => 'Public Speaking'],
            ['skill_name' => 'Graphic Design'],
            ['skill_name' => 'Photography'],
            ['skill_name' => 'Videography'],
            ['skill_name' => 'UI/UX Design'],
            ['skill_name' => 'Copywriting'],
            ['skill_name' => 'Project Management'],
        ]);
    }
}
