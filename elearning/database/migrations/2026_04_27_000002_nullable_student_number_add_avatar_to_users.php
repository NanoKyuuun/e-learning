<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Jadikan student_number nullable
        //    Harus drop unique index dulu sebelum change(), lalu pasang kembali
        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique('students_student_number_unique');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->string('student_number', 50)->nullable()->change();
            $table->unique('student_number');
        });

        // 2. Tambah kolom avatar ke tabel users
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique('students_student_number_unique');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->string('student_number', 50)->nullable(false)->change();
            $table->unique('student_number');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('avatar');
        });
    }
};
