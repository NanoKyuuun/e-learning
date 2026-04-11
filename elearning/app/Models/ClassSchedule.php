<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ClassSchedule extends Model
{
    use HasUuids;

    protected $fillable = [
        'teaching_assignment_id',
        'day',
        'start_time',
        'end_time',
        'room_name',
        'is_active',
    ];

    public function teachingAssignment()
    {
        return $this->belongsTo(TeachingAssignment::class);
    }
}
