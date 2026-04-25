<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel absensi resmi siswa per pertemuan.
     * Satu siswa hanya boleh punya satu record absensi per meeting.
     */
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('meeting_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();

            // Status kehadiran
            $table->enum('status', ['present', 'late', 'failed', 'manual', 'excused', 'absent'])
                ->default('present')
                ->comment('present=hadir, late=terlambat, failed=gagal, manual=manual guru, excused=izin, absent=tidak hadir');

            // Metode absensi
            $table->string('verification_method', 30)
                ->default('face_recognition')
                ->comment('face_recognition, manual, qr');

            // Hasil verifikasi wajah
            $table->boolean('face_verified')->nullable()->comment('Apakah wajah berhasil diverifikasi oleh Python');
            $table->decimal('face_distance', 8, 6)->nullable()->comment('Nilai jarak wajah dari Python (lebih kecil = lebih mirip)');

            // Waktu absensi
            $table->timestamp('check_in_at')->nullable();

            // Data tambahan dari Python dan request
            $table->json('metadata')->nullable()->comment('Response Python, IP, user agent, device');

            $table->timestamps();

            // Satu siswa hanya boleh absen sekali per meeting
            $table->unique(['meeting_id', 'student_id']);
            $table->index(['student_id', 'meeting_id']);
            $table->index('meeting_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
