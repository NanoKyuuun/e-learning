<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFaceProfileRequest;
use App\Jobs\SyncFaceProfileToPython;
use App\Models\FaceProfile;
use App\Models\Student;
use App\Services\FaceRecognitionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class FaceProfileController extends Controller
{
    public function __construct(
        private readonly FaceRecognitionService $faceService
    ) {}

    // ─────────────────────────────────────────────
    // Index: daftar semua siswa + status wajah
    // ─────────────────────────────────────────────

    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status'); // filter by sync_status

        $students = Student::with(['user', 'faceProfile'])
            ->when($search, fn ($q) => $q->whereHas('user', fn ($u) =>
                $u->where('full_name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
            ))
            ->when($status, fn ($q) => $q->whereHas('faceProfile', fn ($fp) =>
                $fp->where('sync_status', $status)
            ))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $apiStatus = $this->faceService->healthCheck();

        return Inertia::render('Admin/FaceProfiles/Index', [
            'students'  => $students,
            'filters'   => $request->only(['search', 'status']),
            'apiStatus' => $apiStatus,
        ]);
    }

    // ─────────────────────────────────────────────
    // Store: daftarkan wajah baru untuk siswa
    // ─────────────────────────────────────────────

    public function store(StoreFaceProfileRequest $request, Student $student)
    {
        // Simpan foto ke storage private
        $path = $request->file('image')->storeAs(
            "faces/students/{$student->id}",
            'profile.jpg',
            'local'
        );

        // Buat atau ganti face profile (updateOrCreate agar tidak duplikat)
        $faceProfile = FaceProfile::updateOrCreate(
            ['student_id' => $student->id],
            [
                'user_id'      => $student->user_id,
                'image_path'   => $path,
                'image_hash'   => hash('sha256', file_get_contents($request->file('image')->getRealPath())),
                'sync_status'  => 'pending',
                'is_active'    => true,
                'sync_error'   => null,
            ]
        );

        // Observer akan otomatis dispatch SyncFaceProfileToPython
        // jika record baru (created) atau image_path berubah (updated)

        return redirect()->back()->with('success', 'Foto wajah berhasil disimpan. Sinkronisasi sedang diproses.');
    }

    // ─────────────────────────────────────────────
    // Update: ganti foto wajah siswa
    // ─────────────────────────────────────────────

    public function update(StoreFaceProfileRequest $request, Student $student)
    {
        $faceProfile = FaceProfile::where('student_id', $student->id)->firstOrFail();

        // Hapus foto lama jika ada
        if ($faceProfile->image_path && Storage::exists($faceProfile->image_path)) {
            Storage::delete($faceProfile->image_path);
        }

        $path = $request->file('image')->storeAs(
            "faces/students/{$student->id}",
            'profile.jpg',
            'local'
        );

        $newHash = hash('sha256', file_get_contents($request->file('image')->getRealPath()));

        $faceProfile->update([
            'image_path'  => $path,
            'image_hash'  => $newHash,
            'sync_status' => 'pending',
            'sync_error'  => null,
            'is_active'   => true,
        ]);

        // Observer akan otomatis dispatch sync karena image_path & image_hash berubah

        return redirect()->back()->with('success', 'Foto wajah berhasil diganti. Sinkronisasi sedang diproses.');
    }

    // ─────────────────────────────────────────────
    // Resync: sinkron ulang satu siswa
    // ─────────────────────────────────────────────

    public function resync(Student $student)
    {
        $faceProfile = FaceProfile::where('student_id', $student->id)
            ->where('is_active', true)
            ->firstOrFail();

        $faceProfile->update([
            'sync_status' => 'pending',
            'sync_error'  => null,
        ]);

        // Dispatch langsung tanpa menunggu observer
        SyncFaceProfileToPython::dispatch($faceProfile->id);

        return redirect()->back()->with('success', 'Sinkronisasi ulang dimulai untuk siswa ini.');
    }

    // ─────────────────────────────────────────────
    // ResyncAll: sinkron ulang semua siswa
    // ─────────────────────────────────────────────

    public function resyncAll()
    {
        $profiles = FaceProfile::where('is_active', true)
            ->whereIn('sync_status', ['pending', 'failed', 'synced'])
            ->get();

        $count = 0;
        foreach ($profiles as $profile) {
            $profile->update(['sync_status' => 'pending', 'sync_error' => null]);
            SyncFaceProfileToPython::dispatch($profile->id);
            $count++;
        }

        return redirect()->back()->with('success', "Sinkronisasi ulang dimulai untuk {$count} siswa.");
    }

    // ─────────────────────────────────────────────
    // Destroy: nonaktifkan data wajah siswa
    // ─────────────────────────────────────────────

    public function destroy(Student $student)
    {
        $faceProfile = FaceProfile::where('student_id', $student->id)->firstOrFail();

        // Update is_active → observer akan dispatch DisableFaceProfileOnPython
        $faceProfile->update(['is_active' => false]);

        return redirect()->back()->with('success', 'Data wajah siswa berhasil dinonaktifkan.');
    }
}
