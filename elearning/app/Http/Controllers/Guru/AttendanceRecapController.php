<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Meeting;
use App\Models\TeachingAssignment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AttendanceRecapController extends Controller
{
    public function index(TeachingAssignment $teachingAssignment)
    {
        // Pastikan guru yang mengakses adalah pengampu
        if ($teachingAssignment->teacher_id !== auth()->user()->teacher->id) {
            abort(403);
        }

        $teachingAssignment->load(['subject', 'classGroup', 'semester.academicYear']);

        // Ambil semua pertemuan untuk assignment ini
        $meetings = Meeting::where('teaching_assignment_id', $teachingAssignment->id)
            ->orderBy('meeting_number')
            ->get();

        // Ambil semua siswa yang terdaftar di kelas ini
        $students = \App\Models\Student::with('user')
            ->whereHas('enrollments', function ($query) use ($teachingAssignment) {
                $query->where('class_group_id', $teachingAssignment->class_group_id)
                    ->where('status', 'active');
            })
            ->orderBy('student_number')
            ->get();

        // Ambil semua data absensi untuk pertemuan-pertemuan ini
        $attendances = Attendance::whereIn('meeting_id', $meetings->pluck('id'))
            ->get()
            ->groupBy('student_id');

        // Format data untuk tabel rekap
        $recapData = $students->map(function ($student) use ($meetings, $attendances) {
            $studentAttendances = $attendances->get($student->id, collect());
            
            $meetingStats = $meetings->map(function ($meeting) use ($studentAttendances) {
                $attendance = $studentAttendances->firstWhere('meeting_id', $meeting->id);
                return [
                    'meeting_id' => $meeting->id,
                    'status' => $attendance ? $attendance->status : 'absent',
                    'check_in_at' => $attendance ? $attendance->check_in_at?->format('H:i') : null,
                ];
            });

            return [
                'student_id' => $student->id,
                'name' => $student->user->full_name,
                'student_number' => $student->student_number,
                'meetings' => $meetingStats,
                'summary' => [
                    'present' => $studentAttendances->whereIn('status', ['present', 'late', 'manual'])->count(),
                    'absent' => $meetings->count() - $studentAttendances->count(),
                    'late' => $studentAttendances->where('status', 'late')->count(),
                ]
            ];
        });

        return Inertia::render('Guru/Attendances/Recap', [
            'teachingAssignment' => $teachingAssignment,
            'meetings' => $meetings,
            'recapData' => $recapData,
        ]);
    }
}
