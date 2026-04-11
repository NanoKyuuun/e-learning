<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AssignmentSubmission extends Model
{
    use HasUuids;

    protected $fillable = [
        'assignment_id',
        'student_id',
        'submitted_at',
        'submission_text',
        'file_url',
        'status',
        'student_notes',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function grade()
    {
        return $this->hasOne(AssignmentGrade::class, 'submission_id');
    }
}
