<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel ini menyimpan data wajah siswa dan status sinkronisasinya ke Python API.
     * Setiap siswa memiliki maksimal satu face_profile aktif.
     */
    public function up(): void
    {
        Schema::create('face_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Relasi ke siswa dan user (redundan tapi mempermudah query)
            $table->foreignUuid('student_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();

            // Data file foto referensi yang disimpan di storage Laravel
            $table->string('image_path', 500)->nullable()->comment('Path foto di storage private');
            $table->string('image_hash', 64)->nullable()->comment('SHA-256 hash file untuk deteksi perubahan');

            // Status sinkronisasi ke Python
            $table->enum('sync_status', ['pending', 'syncing', 'synced', 'failed', 'disabled'])
                ->default('pending')
                ->comment('pending=belum sync, syncing=sedang diproses, synced=berhasil, failed=gagal, disabled=dinonaktifkan');

            $table->timestamp('last_synced_at')->nullable()->comment('Waktu terakhir berhasil sync ke Python');
            $table->text('sync_error')->nullable()->comment('Pesan error dari Python jika sync gagal');

            // Soft toggle tanpa hapus record
            $table->boolean('is_active')->default(true)->comment('false = wajah dinonaktifkan, tidak dipakai untuk absensi');

            $table->timestamps();

            // Satu siswa hanya boleh punya satu face profile aktif
            $table->unique('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('face_profiles');
    }
};
