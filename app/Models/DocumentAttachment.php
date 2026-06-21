<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'vacancy_application_id',
    'file_url',
    'file_name',
])]
class DocumentAttachment extends Model
{
    use HasFactory, HasUlids;

    protected $primaryKey = 'document_attachment_id';

    public function vacancyApplication(): BelongsTo
    {
        return $this->belongsTo(VacancyApplication::class, 'vacancy_application_id', 'vacancy_application_id');
    }
}
