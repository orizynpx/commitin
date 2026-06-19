<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'vacancy_id',
    'status',
    'file_url',
    'interview_scheduled_at',
    'interview_format',
    'interview_location',
    'feedback',
])]
class VacancyApplication extends Model
{
    use HasFactory, HasUlids;

    protected $primaryKey = 'vacancy_application_id';

    protected function casts(): array
    {
        return [
            'interview_scheduled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(Vacancy::class, 'vacancy_id', 'vacancy_id');
    }
}
