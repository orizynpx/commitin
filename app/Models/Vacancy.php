<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['event_id', 'division', 'vacancy_description', 'status'])]
class Vacancy extends Model
{
    use HasFactory, HasUlids;

    protected $primaryKey = 'vacancy_id';

    /** Relasi Balik ke Event terkait */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id', 'event_id');
    }

    /** Relasi Many-to-Many ke Kebutuhan Skill posisi ini */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'skill_vacancy', 'vacancy_id', 'skill_id')
                    ->withTimestamps();
    }

    /** Relasi One-to-Many ke Lamaran yang masuk untuk posisi ini */
    public function applications(): HasMany
    {
        return $this->hasMany(VacancyApplication::class, 'vacancy_id', 'vacancy_id');
    }
}
