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
        // 1. Teaching Assignments
        Schema::create('teaching_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('class_group_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('semester_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('assigned_by')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->unique(['teacher_id', 'class_group_id', 'subject_id', 'semester_id'], 'unique_teaching_assignment');
            $table->timestamps();
        });

        // 2. Class Schedules
        Schema::create('class_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('teaching_assignment_id')->constrained()->cascadeOnDelete();
            $table->string('day', 20); // monday, tuesday, etc.
            $table->time('start_time');
            $table->time('end_time');
            $table->string('room_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Meetings
        Schema::create('meetings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('teaching_assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('schedule_id')->nullable()->constrained('class_schedules')->nullOnDelete();
            $table->integer('meeting_number');
            $table->string('title', 150);
            $table->text('topic')->nullable();
            $table->date('meeting_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('status', 20)->default('draft'); // draft, published, closed
            $table->timestamp('published_at')->nullable();
            $table->foreignUuid('created_by')->constrained('users')->cascadeOnDelete();
            $table->unique(['teaching_assignment_id', 'meeting_number']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meetings');
        Schema::dropIfExists('class_schedules');
        Schema::dropIfExists('teaching_assignments');
    }
};
