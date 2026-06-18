<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\Pivot;

#[Fillable(['event_id', 'user_id', 'organizer_role'])]
class EventOrganizer extends Pivot
{
    protected $table = 'event_organizers';
}
