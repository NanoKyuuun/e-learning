<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Meeting;
use App\Models\StudentClassEnrollment;
use App\Models\TeachingAssignment;
use App\Models\Assignment;
use App\Services\Shared\AcademicService;
use App\Services\Siswa\StudentAcademicService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClassController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly StudentAcademicService $studentAcademicService,
        private readonly AcademicService $academicService
    ) {}

    public function index()
    {
        $student = auth()->user()->student;

        if (!$student) {
            return Inertia::render('Siswa/Subjects/Index', [
                'subjects'   => [],
                'classGroup' => null,
            ]);
        }

        $enrollment = $this->studentAcademicService->getActiveEnrollment($student);

        if (!$enrollment) {
            return Inertia::render('Siswa/Subjects/Index', [
                'subjects'   => [],
                'classGroup' => null,
            ]);
        }

        return Inertia::render('Siswa/Subjects/Index', [
            'subjects'   => $this->studentAcademicService->getCurrentTeachingAssignments($student),
            'classGroup' => $enrollment->classGroup,
        ]);
    }

    public function meetings(TeachingAssignment $teachingAssignment)
    {
        $this->authorize('view', $teachingAssignment);

        return Inertia::render('Siswa/Meetings/Index', [
            'teachingAssignment' => $teachingAssignment->load(['subject', 'teacher.user', 'classGroup']),
            'meetings'           => $teachingAssignment->meetings()
                ->whereIn('status', ['published', 'active', 'completed', 'closed'])
                ->orderBy('meeting_number', 'asc')
                ->get(),
        ]);
    }

    public function showMeeting(Meeting $meeting)
    {
        $this->authorize('view', $meeting);

        $student     = auth()->user()->student;
        $faceProfile = $student?->faceProfile;
        $isEnrolledForAttendance = $student
            ? $student->enrollments()
                ->where('class_group_id', $meeting->teachingAssignment->class_group_id)
                ->where('status', 'active')
                ->exists()
            : false;

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
                'materials' => function ($query) {
                    $query->whereNotNull('published_at')
                        ->orderByDesc('published_at');
                },
                'assignments' => function ($query) {
                    $query->where('status', 'published')
                        ->orderBy('due_at');
                },
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
            'isEnrolledForAttendance' => $isEnrolledForAttendance,
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

        $enrollment = $this->studentAcademicService->getActiveEnrollment($student);

        if (!$enrollment) {
            return Inertia::render('Siswa/Grades/Index', ['subjects' => []]);
        }

        $activeSemester = $this->academicService->getActiveSemester();

        $subjects = TeachingAssignment::with([
            'subject',
            'meetings.assignments.submissions' => function ($query) use ($student) {
                $query->where('student_id', $student->id)->with('grade');
            }
        ])
        ->where('class_group_id', $enrollment->class_group_id)
        ->where('is_active', true)
        ->when($activeSemester, function ($query) use ($activeSemester) {
            $query->where('semester_id', $activeSemester->id);
        })
        ->get();

        return Inertia::render('Siswa/Grades/Index', [
            'subjects'   => $subjects,
            'classGroup' => $enrollment->load('classGroup'),
        ]);
    }
}
