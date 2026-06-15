<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['skill_name'])]
class Skill extends Model
{
    use HasFactory, HasUlids;

    protected $primaryKey = 'skill_id';

    /** Relasi Many-to-Many ke Mahasiswa yang memiliki skill ini */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'skill_user', 'skill_id', 'user_id')
                    ->withTimestamps();
    }

    /** Relasi Many-to-Many ke Lowongan yang membutuhkan skill ini */
    public function vacancies(): BelongsToMany
    {
        return $this->belongsToMany(Vacancy::class, 'skill_vacancy', 'skill_id', 'vacancy_id')
                    ->withTimestamps();
    }
}
