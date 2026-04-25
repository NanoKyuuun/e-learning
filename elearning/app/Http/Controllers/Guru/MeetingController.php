<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\TeachingAssignment;
use App\Services\Guru\MeetingService;
use App\Http\Requests\Guru\StoreMeetingRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MeetingController extends Controller
{
    use AuthorizesRequests;

    protected $meetingService;

    public function __construct(MeetingService $meetingService)
    {
        $this->meetingService = $meetingService;
    }

    public function index(TeachingAssignment $teachingAssignment)
    {
        $this->authorize('view', $teachingAssignment);

        return Inertia::render('Guru/Meetings/Index', [
            'teachingAssignment' => $teachingAssignment->load(['classGroup', 'subject']),
            'meetings' => $this->meetingService->getMeetingsByAssignment($teachingAssignment),
        ]);
    }

    public function store(StoreMeetingRequest $request, TeachingAssignment $teachingAssignment)
    {
        $this->authorize('view', $teachingAssignment);

        $this->meetingService->createMeeting($teachingAssignment, $request->validated());

        return redirect()->back()->with('success', 'Pertemuan berhasil dibuat.');
    }

    public function show(Meeting $meeting)
    {
        $this->authorize('view', $meeting);

        // Eager load dulu agar tidak N+1 dan tidak null pointer
        $meeting->loadMissing('teachingAssignment');
        $classGroupId = $meeting->teachingAssignment->class_group_id;

        $enrolledStudents = \App\Models\Student::with(['user', 'faceProfile'])
            ->whereHas('enrollments', fn ($q) =>
                $q->where('class_group_id', $classGroupId)->where('status', 'active')
            )
            ->get()
            ->map(function ($student) use ($meeting) {
                $attendance = \App\Models\Attendance::where('meeting_id', $meeting->id)
                    ->where('student_id', $student->id)
                    ->first();
                return [
                    'id'          => $student->id,
                    'name'        => $student->user->full_name,
                    'face_ready'  => $student->faceProfile?->isReadyForAttendance() ?? false,
                    'face_status' => $student->faceProfile?->sync_status ?? 'none',
                    'attendance'  => $attendance ? [
                        'status'        => $attendance->status,
                        'face_verified' => $attendance->face_verified,
                        'face_distance' => $attendance->face_distance,
                        'check_in_at'   => $attendance->check_in_at?->format('H:i:s'),
                    ] : null,
                ];
            });

        $attendanceSummary = [
            'total'   => $enrolledStudents->count(),
            'present' => $enrolledStudents->whereNotNull('attendance')->count(),
            'absent'  => $enrolledStudents->whereNull('attendance')->count(),
        ];

        return Inertia::render('Guru/Meetings/Show', [
            'meeting' => $meeting->load([
                'teachingAssignment.classGroup',
                'teachingAssignment.subject',
                'materials',
                'assignments' => function ($query) {
                    $query->withCount('submissions');
                }
            ]),
            'enrolledStudents'  => $enrolledStudents,
            'attendanceSummary' => $attendanceSummary,
        ]);
    }

    public function publish(Meeting $meeting)
    {
        $this->authorize('update', $meeting);
        $this->meetingService->publishMeeting($meeting);
        return redirect()->back()->with('success', 'Pertemuan berhasil dipublikasikan.');
    }

    public function destroy(Meeting $meeting)
    {
        $this->authorize('delete', $meeting);
        $this->meetingService->deleteMeeting($meeting);
        return redirect()->back()->with('success', 'Pertemuan berhasil dihapus.');
    }
}
