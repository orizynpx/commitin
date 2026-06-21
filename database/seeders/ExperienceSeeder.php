<?php

namespace Database\Seeders;

use App\Models\Experience;
use App\Models\User;
use Illuminate\Database\Seeder;

class ExperienceSeeder extends Seeder
{
    public function run(): void
    {
        $bumawati = User::query()->where('email', 'buma@example.com')->first();
        $andi = User::query()->where('email', 'andi@example.com')->first();

        Experience::create([
            'user_id' => $bumawati->user_id,
            'title' => 'Ketua HIMA TI 2028',
            'description' => 'Memimpin organisasi mahasiswa selama satu periode.',
        ]);

        Experience::create([
            'user_id' => $bumawati->user_id,
            'title' => 'Koordinator PKKMB',
            'description' => 'Mengkoordinasikan pelaksanaan PKKMB tingkat fakultas.',
        ]);

        Experience::create([
            'user_id' => $andi->user_id,
            'title' => 'Staff Acara Wasaka Games',
            'description' => 'Bertanggung jawab terhadap pelaksanaan lomba.',
        ]);
    }
}
