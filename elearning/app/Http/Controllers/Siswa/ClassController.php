<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Meeting;
use App\Models\StudentClassEnrollment;
use App\Models\TeachingAssignment;
use App\Models\Assignment;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClassController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $student = auth()->user()->student;
        
        if (!$student) {
            return Inertia::render('Siswa/Subjects/Index', [
                'subjects'   => [],
                'classGroup' => null,
            ]);
        }

        $enrollment = StudentClassEnrollment::with(['classGroup.academicYear'])
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->first();

        if (!$enrollment) {
            return Inertia::render('Siswa/Subjects/Index', [
                'subjects'   => [],
                'classGroup' => null,
            ]);
        }

        $subjects = TeachingAssignment::with(['subject', 'teacher.user'])
            ->where('class_group_id', $enrollment->class_group_id)
            ->where('is_active', true)
            ->get();

        return Inertia::render('Siswa/Subjects/Index', [
            'subjects'   => $subjects,
            'classGroup' => $enrollment->classGroup,
        ]);
    }

    public function meetings(TeachingAssignment $teachingAssignment)
    {
        $this->authorize('view', $teachingAssignment);

        return Inertia::render('Siswa/Meetings/Index', [
            'teachingAssignment' => $teachingAssignment->load(['subject', 'teacher.user', 'classGroup']),
            'meetings'           => $teachingAssignment->meetings()
                ->where('status', 'published')
                ->orWhere('status', 'active')
                ->orderBy('meeting_number', 'asc')
                ->get(),
        ]);
    }

    public function showMeeting(Meeting $meeting)
    {
        $this->authorize('view', $meeting);

        $student     = auth()->user()->student;
        $faceProfile = $student?->faceProfile;

        // Cek apakah siswa sudah absen di meeting ini
        $myAttendance = $student
            ? Attendance::where('meeting_id', $meeting->id)
                ->where('student_id', $student->id)
                ->first()
            : null;

        return Inertia::render('Siswa/Meetings/Show', [
            'meeting' => $meeting->load([
                'teachingAssignment.subject',
                'teachingAssignment.teacher.user',
                'materials',
                'assignments',
            ]),
            // Status absensi siswa di meeting ini
            'myAttendance' => $myAttendance,
            // Status face profile untuk menentukan apakah bisa absen
            'faceProfileStatus' => $faceProfile ? [
                'exists'      => true,
                'sync_status' => $faceProfile->sync_status,
                'is_active'   => $faceProfile->is_active,
                'is_ready'    => $faceProfile->isReadyForAttendance(),
            ] : [
                'exists'      => false,
                'sync_status' => null,
                'is_active'   => false,
                'is_ready'    => false,
            ],
            // Apakah absensi sedang terbuka
            'isAttendanceOpen' => $meeting->isAttendanceOpen(),
        ]);
    }

    public function showAssignment(Assignment $assignment)
    {
        $this->authorize('view', $assignment->meeting);

        $student = auth()->user()->student;

        return Inertia::render('Siswa/Assignments/Show', [
            'assignment' => $assignment->load([
                'meeting.teachingAssignment.subject',
                'meeting.teachingAssignment.subject.department',
                'meeting.teachingAssignment.teacher.user',
            ]),
            'submission' => \App\Models\AssignmentSubmission::with('grade')
                ->where('assignment_id', $assignment->id)
                ->where('student_id', $student->id)
                ->first(),
        ]);
    }

    public function grades()
    {
        $student = auth()->user()->student;
        if (!$student) abort(403);

        $enrollment = StudentClassEnrollment::where('student_id', $student->id)
            ->where('status', 'active')
            ->first();

        if (!$enrollment) {
            return Inertia::render('Siswa/Grades/Index', ['subjects' => []]);
        }

        $subjects = TeachingAssignment::with([
            'subject',
            'meetings.assignments.submissions' => function ($query) use ($student) {
                $query->where('student_id', $student->id)->with('grade');
            }
        ])
        ->where('class_group_id', $enrollment->class_group_id)
        ->get();

        return Inertia::render('Siswa/Grades/Index', [
            'subjects'   => $subjects,
            'classGroup' => $enrollment->load('classGroup'),
        ]);
    }
}
