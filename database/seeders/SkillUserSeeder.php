<?php

namespace Database\Seeders;

use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SkillUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bumawati = User::query()->where('email', 'buma@example.com')->first();
        $andi = User::query()->where('email', 'Andi@example.com')->first();

        $leadership = Skill::query()->where('skill_name', 'Leadership')->first();
        $publicSpeaking = Skill::query()->where('skill_name', 'Public Speaking')->first();
        $graphicDesign = Skill::query()->where('skill_name', 'Graphic Design')->first();

        DB::table('skill_user')->insert([
            [
                'user_id' => $bumawati->user_id,
                'skill_id' => $leadership->skill_id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $bumawati->user_id,
                'skill_id' => $publicSpeaking->skill_id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $andi->user_id,
                'skill_id' => $graphicDesign->skill_id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
