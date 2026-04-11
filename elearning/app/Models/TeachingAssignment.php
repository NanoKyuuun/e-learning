<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TeachingAssignment extends Model
{
    use HasUuids;

    protected $fillable = [
        'teacher_id',
        'class_group_id',
        'subject_id',
        'semester_id',
        'assigned_by',
        'is_active',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function classGroup()
    {
        return $this->belongsTo(ClassGroup::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function meetings()
    {
        return $this->hasMany(Meeting::class);
    }

    public function schedules()
    {
        return $this->hasMany(ClassSchedule::class);
    }
}
