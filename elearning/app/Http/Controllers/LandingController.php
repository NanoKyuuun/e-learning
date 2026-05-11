<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Inertia\Inertia;
use Inertia\Response;

class LandingController extends Controller
{
    public function index(): Response
    {
        $stats = [
            'total_students' => Student::count(),
            'total_teachers' => Teacher::count(),
            'total_subjects' => Subject::count(),
            'total_meetings'  => Meeting::count(),
        ];

        return Inertia::render('Landing', [
            'stats' => $stats,
        ]);
    }
}
