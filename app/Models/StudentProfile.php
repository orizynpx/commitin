<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'student_id', 'faculty', 'study_program', 'entry_year', 'bio'])]
class StudentProfile extends Model
{
    use HasUlids;

    protected $table = 'student_profile';

    protected $primaryKey = 'student_profile_id';

    protected function casts(): array
    {
        return [
            'entry_year' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
