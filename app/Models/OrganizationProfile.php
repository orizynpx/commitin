<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'organization_level', 'description', 'verification_status', 'verified_at'])]
class OrganizationProfile extends Model
{
    use HasUlids;

    protected $table = 'organization_profile';

    protected $primaryKey = 'organization_profile_id';

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
        ];
    }

    /** Relasi balik ke Pengguna */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
