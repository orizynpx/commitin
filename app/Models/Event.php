<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['event_name', 'description', 'event_date', 'is_official'])]
class Event extends Model
{
    use HasFactory, HasUlids;

    protected $primaryKey = 'event_id';

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'is_official' => 'boolean',
        ];
    }

    /** Relasi Many-to-Many ke Mahasiswa yang mengorganisasi Event ini */
    public function organizers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_organizers', 'event_id', 'user_id')
                    ->withPivot('organizer_role')
                    ->withTimestamps();
    }

    /** Relasi One-to-Many ke Lowongan Kepanitiaan di dalam Event ini */
    public function vacancies(): HasMany
    {
        return $this->hasMany(Vacancy::class, 'event_id', 'event_id');
    }
}
