<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventOrganizer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Auth\User;

class EventOrganizerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pkkmb = Event::query()->where('event_name', 'PKKMB 2028')->first();
        $games = Event::query()->where('event_name', 'Wasaka Games 2028')->first();

        $hmti = User::query()->where('email', 'hmti@example.com')->first();
        $wg = User::query()->where('email', 'wg@example.com')->first();

        EventOrganizer::insert([
            [
                'event_id' => $pkkmb->event_id,
                'user_id' => $hmti->user_id,
                'organizer_role' => 'owner',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'event_id' => $games->event_id,
                'user_id' => $wg->user_id,
                'organizer_role' => 'owner',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
