<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    use HasUuids;

    protected $fillable = [
        'academic_year_id',
        'code',
        'name',
        'start_date',
        'end_date',
        'status',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
