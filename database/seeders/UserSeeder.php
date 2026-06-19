<?php
namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'System Admin',
            'email' => 'admin@example.com',
            'password' => 'admin123',
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'HMTI',
            'email' => 'HMTI@example.com',
            'password' => 'password',
            'role' => 'organization',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Wasaka Games',
            'email' => 'WG@example.com',
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
            'email' => 'Andi@example.com',
            'password' => 'password',
            'role' => 'student',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Wasaka Robotic',
            'email' => 'Robotic@example.com',
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
