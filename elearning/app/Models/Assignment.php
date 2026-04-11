<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Assignment extends Model
{
    use HasUuids;

    protected $fillable = [
        'meeting_id',
        'title',
        'instructions',
        'file_url',
        'open_at',
        'due_at',
        'max_score',
        'submission_type',
        'status',
        'created_by',
    ];

    protected $casts = [
        'open_at' => 'datetime',
        'due_at' => 'datetime',
    ];

    protected $appends = ['formatted_due_date'];

    public function getFormattedDueDateAttribute()
    {
        return $this->due_at ? $this->due_at->translatedFormat('d F Y, H:i') . ' WIB' : '-';
    }

    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }

    public function submissions()
    {
        return $this->hasMany(AssignmentSubmission::class);
    }
}
