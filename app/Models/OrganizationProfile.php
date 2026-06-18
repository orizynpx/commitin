<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class OrganizationProfile extends Model
{
    use HasUlids;

    protected $table = 'organization_profile';

     protected $primaryKey = 'organization_profile_id';

    protected $fillable = [
        'user_id',
        'organization_level',
        'description',
        'verification_status',
        'verified_at',
    ];
}
