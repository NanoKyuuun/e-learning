<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'status',
    ];

    public function semesters()
    {
        return $this->hasMany(Semester::class);
    }

    public function classGroups()
    {
        return $this->hasMany(ClassGroup::class);
    }
}
