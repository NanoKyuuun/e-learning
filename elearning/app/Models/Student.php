<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'student_number',
        'phone',
        'gender',
        'is_active',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function enrollments()
    {
        return $this->hasMany(StudentClassEnrollment::class);
    }

    public function submissions()
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function faceProfile()
    {
        return $this->hasOne(FaceProfile::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function attendanceAttempts()
    {
        return $this->hasMany(AttendanceAttempt::class);
    }
}
