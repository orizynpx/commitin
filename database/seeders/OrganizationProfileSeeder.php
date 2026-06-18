<?php

namespace Database\Seeders;

use App\Models\OrganizationProfile;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrganizationProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hmti = User::query()->where('email', 'hmti@example.com')->first();
        $wg = User::query()->where('email', 'wg@example.com')->first();
        $robotic = User::query()->where('email', 'robotic@example.com')->first();

        OrganizationProfile::create([
            [
                'user_id' => $hmti->user_id,
                'organization_level' => 'study_program',
                'description' => 'Himpunan Mahasiswa Teknik Informatika.',
                'verification_status' => 'verified',
                'verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],


        ]);
        OrganizationProfile::create([
            [
                'user_id' => $wg->user_id,
                'organization_level' => 'faculty',
                'description' => 'Komunitas penyelenggara kompetisi game.',
                'verification_status' => 'verified',
                'verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
        OrganizationProfile::create([
            [
                'user_id' => $robotic->user_id,
                'organization_level' => 'university',
                'description' => 'Komunitas robotika universitas.',
                'verification_status' => 'pending',
                'verified_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
