<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AssignmentGrade extends Model
{
    use HasUuids;

    protected $fillable = [
        'submission_id',
        'graded_by_teacher_id',
        'score',
        'feedback',
        'graded_at',
    ];

    protected $casts = [
        'graded_at' => 'datetime',
    ];

    public function submission()
    {
        return $this->belongsTo(AssignmentSubmission::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'graded_by_teacher_id');
    }
}
