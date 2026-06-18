<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class StudentProfile extends Model
{

    use HasUlids;

    protected $table = 'student_profile';

    protected $primaryKey = 'student_profile_id';

    protected $fillable = [
        'user_id',
        'student_id',
        'faculty',
        'study_program',
        'entry_year',
        'bio',
    ];
}
