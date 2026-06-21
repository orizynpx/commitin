<?php

namespace Database\Seeders;

use App\Models\OrganizationProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrganizationProfileSeeder extends Seeder
{
    public function run(): void
    {
        $hmti = User::query()->where('email', 'hmti@example.com')->first();
        $wg = User::query()->where('email', 'wg@example.com')->first();
        $robotic = User::query()->where('email', 'robotic@example.com')->first();

        OrganizationProfile::create([
            'user_id' => $hmti->user_id,
            'organization_level' => 'study_program',
            'description' => 'Himpunan Mahasiswa Teknik Informatika.',
            'verification_status' => 'verified',
            'verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        OrganizationProfile::create([
            'user_id' => $wg->user_id,
            'organization_level' => 'faculty',
            'description' => 'Komunitas penyelenggara kompetisi game.',
            'verification_status' => 'verified',
            'verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        OrganizationProfile::create([
            'user_id' => $robotic->user_id,
            'organization_level' => 'university',
            'description' => 'Komunitas robotika universitas.',
            'verification_status' => 'pending',
            'verified_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $blocked_org = User::query()->where('email', 'blocked_org@example.com')->first();
        $rejected_org = User::query()->where('email', 'rejected_org@example.com')->first();

        OrganizationProfile::create([
            'user_id' => $blocked_org->user_id,
            'organization_level' => 'faculty',
            'description' => 'Akun organisasi yang diblokir.',
            'verification_status' => 'verified',
            'verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        OrganizationProfile::create([
            'user_id' => $rejected_org->user_id,
            'organization_level' => 'university',
            'description' => 'Akun organisasi yang ditolak verifikasinya.',
            'verification_status' => 'rejected',
            'verified_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
