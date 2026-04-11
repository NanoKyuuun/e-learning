<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Departments
        Schema::create('departments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 50)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Academic Years
        Schema::create('academic_years', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 20)->unique(); // e.g. 2026/2027
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 20)->default('active'); // active, inactive, archived
            $table->timestamps();
        });

        // 3. Semesters
        Schema::create('semesters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('academic_year_id')->constrained()->cascadeOnDelete();
            $table->string('code', 20); // ganjil, genap
            $table->string('name', 50);
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 20)->default('active');
            $table->unique(['academic_year_id', 'code']);
            $table->timestamps();
        });

        // 4. Teachers
        Schema::create('teachers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignUuid('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('employee_number', 50)->unique()->nullable();
            $table->string('phone', 30)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 5. Students
        Schema::create('students', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('student_number', 50)->unique();
            $table->string('phone', 30)->nullable();
            $table->string('gender', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 6. Subjects
        Schema::create('subjects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code', 50)->unique();
            $table->string('name', 150);
            $table->integer('grade_level')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 7. Class Groups
        Schema::create('class_groups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('department_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('homeroom_teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->integer('grade_level');
            $table->integer('capacity')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 8. Student Class Enrollments
        Schema::create('student_class_enrollments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('class_group_id')->constrained()->cascadeOnDelete();
            $table->date('enrolled_at');
            $table->string('status', 30)->default('active');
            $table->text('notes')->nullable();
            $table->unique(['student_id', 'class_group_id']);
            $table->timestamps();
        });

        // 9. Department Head Assignments (Kajur)
        Schema::create('department_head_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('department_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete(); // User with Kajur role
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignUuid('appointed_by')->constrained('users')->cascadeOnDelete();
            $table->unique(['department_id', 'user_id', 'start_date'], 'dept_head_unique');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('department_head_assignments');
        Schema::dropIfExists('student_class_enrollments');
        Schema::dropIfExists('class_groups');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('students');
        Schema::dropIfExists('teachers');
        Schema::dropIfExists('semesters');
        Schema::dropIfExists('academic_years');
        Schema::dropIfExists('departments');
    }
};
