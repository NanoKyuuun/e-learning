<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    use HasUuids;

    protected $fillable = [
        'teaching_assignment_id',
        'schedule_id',
        'meeting_number',
        'title',
        'topic',
        'meeting_date',
        'start_time',
        'end_time',
        'status',
        'published_at',
        'created_by',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'meeting_date' => 'date',
    ];

    public function teachingAssignment()
    {
        return $this->belongsTo(TeachingAssignment::class);
    }

    public function materials()
    {
        return $this->hasMany(Material::class);
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }
}
