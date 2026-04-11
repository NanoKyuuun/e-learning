<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasUuids;

    protected $fillable = [
        'department_id',
        'code',
        'name',
        'grade_level',
        'description',
        'is_active',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function teachingAssignments()
    {
        return $this->hasMany(TeachingAssignment::class, 'subject_id');
    }
}
