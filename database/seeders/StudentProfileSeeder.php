<?php

namespace Database\Seeders;

use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StudentProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bumawati = User::query()->where('email', 'buma@example.com')->first();
        $andi = User::query()->where('email', 'andi@example.com')->first();

        StudentProfile::create([
            [
                'user_id' => $bumawati->user_id,
                'student_id' => 'STUDENT01',
                'faculty' => 'Teknik',
                'study_program' => 'Teknik Informatika',
                'entry_year' => 2024,
                'bio' => 'Aktif dalam organisasi dan kepanitiaan.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
        StudentProfile::create([
                'user_id' => $andi->user_id,
                'student_id' => 'STUDENT02',
                'faculty' => 'Teknik',
                'study_program' => 'Sistem Informasi',
                'entry_year' => 2024,
                'bio' => 'Tertarik pada manajemen acara.',
                'created_at' => now(),
                'updated_at' => now(),
        ]);
    }
}
