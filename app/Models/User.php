<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'student_id', 'email', 'password', 'role', 'is_verified'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasUlids;

    protected $primaryKey = 'user_id';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_verified' => 'boolean',
        ];
    }

    /** Relasi ke Pengalaman Kerja/Organisasi Mahasiswa */
    public function experiences(): HasMany
    {
        return $this->hasMany(Experience::class, 'user_id', 'user_id');
    }

    /** Relasi Many-to-Many ke Skill yang dikuasai Mahasiswa */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'skill_user', 'user_id', 'skill_id')
                    ->withTimestamps();
    }

    /** Relasi Many-to-Many ke Event sebagai Penyelenggara (Ormawa/Kepanitiaan) */
    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_organizers', 'user_id', 'event_id')
                    ->withPivot('organizer_role')
                    ->withTimestamps();
    }

    /** Relasi ke Lamaran Lowongan yang diajukan Mahasiswa */
    public function vacancyApplications(): HasMany
    {
        return $this->hasMany(VacancyApplication::class, 'user_id', 'user_id');
    }
}
