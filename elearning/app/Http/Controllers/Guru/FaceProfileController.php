<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Jobs\SyncFaceProfileToPython;
use App\Models\FaceProfile;
use App\Models\Student;
use App\Models\TeachingAssignment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FaceProfileController extends Controller
{
    // ─────────────────────────────────────────────
    // Index: daftar siswa di kelas guru + status wajah
    // ─────────────────────────────────────────────

    public function index(TeachingAssignment $teachingAssignment)
    {
        // Pastikan guru memiliki hak akses ke kelas ini
        $this->authorizeTeacherAccess($teachingAssignment);

        // Ambil semua siswa yang enrolled di class_group ini
        $students = Student::with(['user', 'faceProfile'])
            ->whereHas('enrollments', fn ($q) =>
                $q->where('class_group_id', $teachingAssignment->class_group_id)
                  ->where('status', 'active')
            )
            ->get();

        return Inertia::render('Guru/FaceProfiles/Index', [
            'teachingAssignment' => $teachingAssignment->load(['classGroup', 'subject']),
            'students'           => $students,
        ]);
    }

    // ─────────────────────────────────────────────
    // Resync: sinkron ulang satu siswa (validasi kelas)
    // ─────────────────────────────────────────────

    public function resync(TeachingAssignment $teachingAssignment, Student $student)
    {
        $this->authorizeTeacherAccess($teachingAssignment);

        // Pastikan siswa memang ada di kelas yang diajar guru ini
        $isInClass = $student->enrollments()
            ->where('class_group_id', $teachingAssignment->class_group_id)
            ->where('status', 'active')
            ->exists();

        if (! $isInClass) {
            return redirect()->back()->with('error', 'Siswa tidak terdaftar di kelas ini.');
        }

        $faceProfile = FaceProfile::where('student_id', $student->id)
            ->where('is_active', true)
            ->firstOrFail();

        $faceProfile->update([
            'sync_status' => 'pending',
            'sync_error'  => null,
        ]);

        SyncFaceProfileToPython::dispatch($faceProfile->id);

        return redirect()->back()->with('success', 'Sinkronisasi ulang dimulai.');
    }

    // ─────────────────────────────────────────────
    // ResyncClass: sinkron ulang semua siswa di kelas
    // ─────────────────────────────────────────────

    public function resyncClass(TeachingAssignment $teachingAssignment)
    {
        $this->authorizeTeacherAccess($teachingAssignment);

        $studentIds = Student::whereHas('enrollments', fn ($q) =>
            $q->where('class_group_id', $teachingAssignment->class_group_id)
              ->where('status', 'active')
        )->pluck('id');

        $profiles = FaceProfile::whereIn('student_id', $studentIds)
            ->where('is_active', true)
            ->get();

        $count = 0;
        foreach ($profiles as $profile) {
            $profile->update(['sync_status' => 'pending', 'sync_error' => null]);
            SyncFaceProfileToPython::dispatch($profile->id);
            $count++;
        }

        return redirect()->back()->with('success', "Sinkronisasi ulang dimulai untuk {$count} siswa di kelas ini.");
    }

    // ─────────────────────────────────────────────
    // Private: validasi kepemilikan kelas
    // ─────────────────────────────────────────────

    private function authorizeTeacherAccess(TeachingAssignment $teachingAssignment): void
    {
        $teacher = auth()->user()->teacher;

        abort_if(
            ! $teacher || $teachingAssignment->teacher_id !== $teacher->id,
            403,
            'Anda tidak memiliki akses ke kelas ini.'
        );
    }
}
