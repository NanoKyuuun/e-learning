<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ClassGroup extends Model
{
    use HasUuids;

    protected $fillable = [
        'department_id',
        'academic_year_id',
        'homeroom_teacher_id',
        'code',
        'name',
        'grade_level',
        'capacity',
        'is_active',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function homeroomTeacher()
    {
        return $this->belongsTo(Teacher::class, 'homeroom_teacher_id');
    }

    public function enrollments()
    {
        return $this->hasMany(StudentClassEnrollment::class, 'class_group_id');
    }

    public function teachingAssignments()
    {
        return $this->hasMany(TeachingAssignment::class, 'class_group_id');
    }
}
