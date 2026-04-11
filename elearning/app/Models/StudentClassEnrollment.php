<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class StudentClassEnrollment extends Model
{
    use HasUuids;

    protected $fillable = [
        'student_id',
        'class_group_id',
        'enrolled_at',
        'status',
        'notes',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function classGroup()
    {
        return $this->belongsTo(ClassGroup::class);
    }
}
