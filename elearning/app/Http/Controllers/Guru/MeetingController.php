<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Ai\AiDocument;
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

        // Muat meeting termasuk materials
        $meeting->load([
            'teachingAssignment.classGroup',
            'teachingAssignment.subject',
            'materials',
            'assignments' => function ($query) {
                $query->withCount('submissions');
            }
        ]);

        // Gabungkan semua materials dengan status AI-nya
        // Sehingga guru bisa melihat semua materi sekaligus + statusnya
        $aiDocsByMaterial = AiDocument::where('meeting_id', $meeting->id)
            ->whereNotNull('material_id')
            ->get()
            ->keyBy('material_id');

        // Materials yang sudah punya AiDocument
        $mergedDocuments = $meeting->materials->map(function ($material) use ($aiDocsByMaterial, $meeting) {
            $aiDoc = $aiDocsByMaterial->get($material->id);
            return [
                // Material source info
                'material_id'      => $material->id,
                'material_title'   => $material->title,
                'material_file_url'=> $material->file_url,
                // AiDocument info (null jika belum pernah diproses)
                'id'               => $aiDoc?->id,
                'title'            => $aiDoc?->title ?? $material->title,
                'original_filename'=> $aiDoc?->original_filename ?? basename($material->file_url ?? ''),
                'file_extension'   => $aiDoc?->file_extension,
                'processing_status'=> $aiDoc?->processing_status ?? 'not_started',
                'total_chunks'     => $aiDoc?->total_chunks ?? 0,
                'total_pages'      => $aiDoc?->total_pages,
                'total_sheets'     => $aiDoc?->total_sheets,
                'error_message'    => $aiDoc?->error_message,
            ];
        });

        // AiDocument yang tidak punya material (diunggah langsung ke AI)
        $orphanAiDocs = AiDocument::where('meeting_id', $meeting->id)
            ->whereNull('material_id')
            ->get()
            ->map(fn ($d) => [
                'material_id'      => null,
                'material_title'   => null,
                'material_file_url'=> null,
                'id'               => $d->id,
                'title'            => $d->title,
                'original_filename'=> $d->original_filename,
                'file_extension'   => $d->file_extension,
                'processing_status'=> $d->processing_status,
                'total_chunks'     => $d->total_chunks,
                'total_pages'      => $d->total_pages,
                'total_sheets'     => $d->total_sheets,
                'error_message'    => $d->error_message,
            ]);

        return Inertia::render('Guru/Meetings/Show', [
            'meeting'           => $meeting,
            'enrolledStudents'  => $enrolledStudents,
            'attendanceSummary' => $attendanceSummary,
            'aiDocuments'       => $mergedDocuments->merge($orphanAiDocs)->values(),
        ]);
    }

    public function publish(Meeting $meeting)
    {
        $this->authorize('update', $meeting);
        $this->meetingService->publishMeeting($meeting);
        return redirect()->back()->with('success', 'Pertemuan berhasil dipublikasikan.');
    }

    public function activate(Meeting $meeting)
    {
        $this->authorize('update', $meeting);
        $this->meetingService->activateMeeting($meeting);
        return redirect()->back()->with('success', 'Absensi berhasil dibuka! Siswa sekarang dapat melakukan presensi wajah.');
    }

    public function close(Meeting $meeting)
    {
        $this->authorize('update', $meeting);
        $this->meetingService->closeMeeting($meeting);
        return redirect()->back()->with('success', 'Absensi berhasil ditutup. Pertemuan selesai.');
    }

    public function destroy(Meeting $meeting)
    {
        $this->authorize('delete', $meeting);
        $this->meetingService->deleteMeeting($meeting);
        return redirect()->back()->with('success', 'Pertemuan berhasil dihapus.');
    }
}
