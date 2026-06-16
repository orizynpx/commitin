<?php

namespace Database\Seeders;

use App\Models\Experience;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExperienceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bumawati = User::query()->where('student_id', 'STUDENT01')->first();
        Experience::create([
            'user_id' => $bumawati->user_id,
            'title' => 'Ketua HIMA TI 2028',
            'description' => 'Memimpin organisasi mahasiswa selama satu periode.',
        ]);
    }
}
