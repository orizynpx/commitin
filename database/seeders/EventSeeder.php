<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       Event::create([
            'event_name' => 'PKKMB 2028',
            'description' => 'Kegiatan penyambutan mahasiswa baru.',
            'event_date' => '2028-08-01',
            'is_official' => true,
        ]);

        Event::create([
            'event_name' => 'Wasaka Games 2028',
            'description' => 'Kompetisi game antar mahasiswa.',
            'event_date' => '2028-06-15',
            'is_official' => false,
        ]);
    }
}
