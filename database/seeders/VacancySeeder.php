<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Vacancy;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VacancySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pkkmb = Event::query()->where('event_name', 'PKKMB 2028')->first();
        $wg = Event::query()->where('event_name', 'Wasaka Games 2028')->first();

        Vacancy::create([
            [
                'event_id' => $pkkmb->event_id,
                'division' => 'Acara',
                'vacancy_description' => 'Membantu pelaksanaan kegiatan.',
                'status' => 'OPEN',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'event_id' => $wg->event_id,
                'division' => 'Publikasi',
                'vacancy_description' => 'Membuat konten promosi acara.',
                'status' => 'OPEN',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
