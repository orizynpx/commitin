<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Administrator',
            'email' => env('ADMIN_EMAIL', 'admin@example.com'),
            'password' => env('ADMIN_PASSWORD', 'admin123'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'HMTI',
            'email' => 'hmti@example.com',
            'password' => 'password',
            'role' => 'organization',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Wasaka Games',
            'email' => 'wg@example.com',
            'password' => 'password',
            'role' => 'organization',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Bumawati',
            'email' => 'buma@example.com',
            'password' => 'password',
            'role' => 'student',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Andi',
            'email' => 'andi@example.com',
            'password' => 'password',
            'role' => 'student',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Wasaka Robotic',
            'email' => 'robotic@example.com',
            'password' => 'password',
            'role' => 'organization',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Blocked Student',
            'email' => 'blocked_student@example.com',
            'password' => 'password',
            'role' => 'student',
            'email_verified_at' => now(),
            'blocked_at' => now(),
            'block_reason' => 'Melanggar syarat dan ketentuan penggunaan platform.',
        ]);

        User::create([
            'name' => 'Blocked Organization',
            'email' => 'blocked_org@example.com',
            'password' => 'password',
            'role' => 'organization',
            'email_verified_at' => now(),
            'blocked_at' => now(),
            'block_reason' => 'Melakukan spamming lowongan.',
        ]);

        User::create([
            'name' => 'Rejected Organization',
            'email' => 'rejected_org@example.com',
            'password' => 'password',
            'role' => 'organization',
            'email_verified_at' => now(),
        ]);
    }
}
