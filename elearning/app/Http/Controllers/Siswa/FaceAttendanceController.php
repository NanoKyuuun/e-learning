<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Http\Requests\Siswa\StoreFaceAttendanceRequest;
use App\Models\Attendance;
use App\Models\AttendanceAttempt;
use App\Models\Meeting;
use App\Services\FaceRecognitionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FaceAttendanceController extends Controller
{
    public function __construct(
        private readonly FaceRecognitionService $faceService
    ) {}

    /**
     * Proses absensi kamera siswa.
     *
     * Flow:
     * 1. Validasi 8 kondisi Laravel sebelum memanggil Python
     * 2. Kirim foto ke Python untuk verifikasi
     * 3. Simpan attendance jika berhasil
     * 4. Selalu simpan attendance_attempt untuk audit trail
     */
    public function store(StoreFaceAttendanceRequest $request, Meeting $meeting)
    {
        $user    = auth()->user();
        $student = $user->student;

        // ─── Validasi 1: Siswa harus punya profil ─────────────────────────
        if (! $student) {
            return response()->json([
                'success' => false,
                'message' => 'Profil siswa tidak ditemukan. Hubungi admin.',
            ], 422);
        }

        // ─── Validasi 2: Meeting harus aktif ──────────────────────────────
        if (! $meeting->isAttendanceOpen()) {
            return response()->json([
                'success' => false,
                'message' => 'Absensi belum dibuka atau sudah ditutup untuk pertemuan ini.',
            ], 422);
        }

        // ─── Validasi 3: Siswa harus terdaftar di kelas tersebut ──────────
        $classGroupId = $meeting->teachingAssignment->class_group_id;
        $isEnrolled   = $student->enrollments()
            ->where('class_group_id', $classGroupId)
            ->where('status', 'active')
            ->exists();

        if (! $isEnrolled) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak terdaftar di kelas ini.',
            ], 403);
        }

        // ─── Validasi 4: Belum pernah absen pada meeting ini ──────────────
        $alreadyAttended = Attendance::where('meeting_id', $meeting->id)
            ->where('student_id', $student->id)
            ->exists();

        if ($alreadyAttended) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan absensi pada pertemuan ini.',
            ], 422);
        }

        // ─── Validasi 5: Harus punya face profile ─────────────────────────
        $faceProfile = $student->faceProfile;

        if (! $faceProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Data wajah Anda belum terdaftar. Hubungi admin atau guru.',
            ], 422);
        }

        // ─── Validasi 6: Face profile harus aktif dan sudah synced ────────
        if (! $faceProfile->isReadyForAttendance()) {
            $statusMsg = match ($faceProfile->sync_status) {
                'pending', 'syncing' => 'Data wajah Anda sedang diproses. Coba beberapa saat lagi.',
                'failed'   => 'Data wajah Anda gagal disinkronkan. Hubungi guru atau admin.',
                'disabled' => 'Data wajah Anda dinonaktifkan. Hubungi admin.',
                default    => 'Data wajah Anda belum siap. Hubungi admin.',
            };

            return response()->json([
                'success' => false,
                'message' => $statusMsg,
            ], 422);
        }

        // ─── Kirim ke Python untuk verifikasi ─────────────────────────────
        $result = $this->faceService->verify(
            (string) $student->id,
            $request->file('image')
        );

        // Metadata untuk disimpan
        $metadata = [
            'python_response' => $result,
            'ip'              => $request->ip(),
            'user_agent'      => $request->userAgent(),
            'source'          => 'web_camera',
        ];

        // ─── Jika Python error teknis (koneksi dll) ────────────────────────
        if ($result['success'] === false && ($result['error_code'] ?? '') === 'CONNECTION_ERROR') {
            $this->saveAttempt($meeting->id, $student->id, $user->id, false, 'PYTHON_CONNECTION_ERROR', $result, $metadata);

            return response()->json([
                'success' => false,
                'message' => 'Layanan pengenalan wajah sedang tidak tersedia. Coba beberapa saat lagi.',
            ], 503);
        }

        // ─── Jika Python error teknis lainnya (422, 404) ──────────────────
        if ($result['success'] === false) {
            $reason = $result['error_code'] ?? 'UNKNOWN';

            $this->saveAttempt($meeting->id, $student->id, $user->id, false, $reason,
                $result['distance'] ?? null, $result['face_count'] ?? null, $metadata);

            $userMessage = match ($reason) {
                'NO_FACE_IN_ATTENDANCE_IMAGE'       => 'Tidak ada wajah terdeteksi. Pastikan wajah Anda terlihat jelas.',
                'MULTIPLE_FACES_IN_ATTENDANCE_IMAGE' => 'Terdeteksi lebih dari satu wajah. Pastikan hanya Anda yang ada di kamera.',
                'NO_PROFILE_FOUND'                  => 'Data wajah Anda tidak ditemukan di sistem. Hubungi admin.',
                default                             => 'Terjadi kesalahan saat memproses wajah. Coba lagi.',
            };

            return response()->json([
                'success' => false,
                'message' => $userMessage,
            ], 422);
        }

        // ─── Python sukses proses: cek verified ───────────────────────────
        $verified    = (bool) ($result['verified'] ?? false);
        $distance    = $result['distance'] ?? null;
        $faceCount   = $result['face_count'] ?? null;

        if (! $verified) {
            $this->saveAttempt($meeting->id, $student->id, $user->id, false, 'FACE_NOT_MATCH', $distance, $faceCount, $metadata);

            return response()->json([
                'success'  => false,
                'verified' => false,
                'message'  => 'Wajah tidak cocok dengan akun Anda.',
                'distance' => $distance,
            ], 422);
        }

        // ─── Berhasil: simpan absensi ──────────────────────────────────────
        try {
            DB::transaction(function () use ($meeting, $student, $user, $distance, $metadata) {
                Attendance::create([
                    'meeting_id'          => $meeting->id,
                    'student_id'          => $student->id,
                    'user_id'             => $user->id,
                    'status'              => 'present',
                    'verification_method' => 'face_recognition',
                    'face_verified'       => true,
                    'face_distance'       => $distance,
                    'check_in_at'         => now(),
                    'metadata'            => $metadata,
                ]);

                // Simpan juga di attendance_attempts sebagai record sukses
                AttendanceAttempt::create([
                    'meeting_id'    => $meeting->id,
                    'student_id'    => $student->id,
                    'user_id'       => $user->id,
                    'success'       => true,
                    'reason'        => null,
                    'face_distance' => $distance,
                    'face_count'    => 1,
                    'metadata'      => $metadata,
                ]);
            });
        } catch (\Exception $e) {
            Log::error('[FaceAttendance] Gagal menyimpan absensi.', [
                'student_id' => $student->id,
                'meeting_id' => $meeting->id,
                'error'      => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Absensi berhasil diverifikasi tetapi gagal disimpan. Hubungi guru.',
            ], 500);
        }

        return response()->json([
            'success'  => true,
            'verified' => true,
            'message'  => 'Absensi berhasil dicatat.',
            'data'     => [
                'status'        => 'present',
                'face_verified' => true,
                'face_distance' => round($distance, 4),
                'check_in_at'   => now()->toIso8601String(),
            ],
        ]);
    }

    // ─────────────────────────────────────────────
    // Private Helper
    // ─────────────────────────────────────────────

    private function saveAttempt(
        string $meetingId,
        string $studentId,
        string $userId,
        bool $success,
        ?string $reason,
        mixed $faceDistanceOrResult,
        ?int $faceCount,
        array $metadata
    ): void {
        try {
            AttendanceAttempt::create([
                'meeting_id'    => $meetingId,
                'student_id'    => $studentId,
                'user_id'       => $userId,
                'success'       => $success,
                'reason'        => $reason,
                'face_distance' => is_numeric($faceDistanceOrResult) ? $faceDistanceOrResult : null,
                'face_count'    => $faceCount,
                'metadata'      => $metadata,
            ]);
        } catch (\Exception $e) {
            Log::warning('[FaceAttendance] Gagal simpan attendance attempt.', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
