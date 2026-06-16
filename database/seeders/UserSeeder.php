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
            'student_id' => 'ADMIN001',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => 'sysadmin',
            'is_verified' => true,
        ]);

        User::create([
            'name' => 'Budi Santoso',
            'student_id' => 'STUDENT01',
            'email' => 'budi@example.com',
            'password' => 'password',
            'role' => 'user',
            'is_verified' => true,
        ]);

        User::create([
            'name' => 'Siti Rahma',
            'student_id' => 'STUDENT02',
            'email' => 'siti@example.com',
            'password' => 'password',
            'role' => 'user',
            'is_verified' => true,
        ]);

        User::create([
            'name' => 'Bumawati',
            'student_id' => 'STUDENT03',
            'email' => 'buma@example.com',
            'password' => 'password',
            'role' => 'user',
            'is_verified' => false,
        ]);
        User::create([
            'name' => 'Andi',
            'student_id' => 'STUDENT04',
            'email' => 'Andi@example.com',
            'password' => 'password',
            'role' => 'user',
            'is_verified' => false,
        ]);
        User::create([
            'name' => 'Rahmadi',
            'student_id' => 'STUDENT05',
            'email' => 'Rahmadi@example.com',
            'password' => 'password',
            'role' => 'user',
            'is_verified' => true,
        ]);
    }
}
