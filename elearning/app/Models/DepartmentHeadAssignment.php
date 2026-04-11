<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class DepartmentHeadAssignment extends Model
{
    use HasUuids;

    protected $fillable = [
        'department_id',
        'user_id',
        'start_date',
        'end_date',
        'is_active',
        'appointed_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function appointer()
    {
        return $this->belongsTo(User::class, 'appointed_by');
    }
}
