<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel log semua percobaan absensi termasuk yang gagal.
     * Tabel ini terpisah dari attendances agar data absensi resmi tetap bersih.
     * Berguna untuk audit trail jika siswa mengklaim sudah mencoba absen.
     */
    public function up(): void
    {
        Schema::create('attendance_attempts', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('meeting_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();

            // Hasil percobaan
            $table->boolean('success')->default(false)->comment('true jika percobaan ini berhasil dan absensi dicatat');
            $table->string('reason', 200)->nullable()->comment('Alasan gagal: FACE_NOT_MATCH, NO_FACE_DETECTED, dll');

            // Data dari Python
            $table->decimal('face_distance', 8, 6)->nullable();
            $table->tinyInteger('face_count')->nullable();

            // Data tambahan
            $table->json('metadata')->nullable()->comment('IP, user agent, python response, dll');

            $table->timestamps();

            $table->index(['student_id', 'meeting_id']);
            $table->index('meeting_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_attempts');
    }
};
