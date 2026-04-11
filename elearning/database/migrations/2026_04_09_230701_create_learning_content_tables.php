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
        // 1. Materials
        Schema::create('materials', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('meeting_id')->constrained()->cascadeOnDelete();
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->text('file_url')->nullable();
            $table->string('file_type', 50)->nullable();
            $table->timestamp('published_at')->nullable();
            $table->foreignUuid('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // 2. Assignments
        Schema::create('assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('meeting_id')->constrained()->cascadeOnDelete();
            $table->string('title', 150);
            $table->text('instructions')->nullable();
            $table->timestamp('open_at')->nullable();
            $table->timestamp('due_at');
            $table->decimal('max_score', 5, 2)->default(100);
            $table->string('submission_type', 50)->nullable(); // file, text, etc.
            $table->string('status', 20)->default('draft');
            $table->foreignUuid('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // 3. Assignment Submissions
        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained()->cascadeOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->text('submission_text')->nullable();
            $table->text('file_url')->nullable();
            $table->string('status', 30)->default('not_submitted'); // submitted, late, returned
            $table->text('student_notes')->nullable();
            $table->unique(['assignment_id', 'student_id']);
            $table->timestamps();
        });

        // 4. Assignment Grades
        Schema::create('assignment_grades', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('submission_id')->unique()->constrained('assignment_submissions')->cascadeOnDelete();
            $table->foreignUuid('graded_by_teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->decimal('score', 5, 2);
            $table->text('feedback')->nullable();
            $table->timestamp('graded_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_grades');
        Schema::dropIfExists('assignment_submissions');
        Schema::dropIfExists('assignments');
        Schema::dropIfExists('materials');
    }
};
