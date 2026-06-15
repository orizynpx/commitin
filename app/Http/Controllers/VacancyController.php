<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VacancyController extends Controller
{
    public function index()
    {
        return response()->json([
            [
                'vacancy_id' => 'VAC-001',
                'event_id' => 'EVT-101',
                'division' => 'Graphic Designer',
                'vacancy_description' => 'Create promotional assets.',
                'status' => 'open',
                'skills' => ['Figma', 'Canva']
            ]
        ], 200);
    }
}
