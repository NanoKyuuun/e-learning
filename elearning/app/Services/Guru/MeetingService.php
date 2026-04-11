<?php

namespace App\Services\Guru;

use App\Models\Meeting;
use App\Models\TeachingAssignment;

class MeetingService
{
    public function getMeetingsByAssignment(TeachingAssignment $teachingAssignment)
    {
        return Meeting::where('teaching_assignment_id', $teachingAssignment->id)
            ->orderBy('meeting_number', 'asc')
            ->get();
    }

    public function createMeeting(TeachingAssignment $teachingAssignment, array $data)
    {
        return Meeting::create([
            'teaching_assignment_id' => $teachingAssignment->id,
            'meeting_number' => $data['meeting_number'],
            'title' => $data['title'],
            'topic' => $data['topic'] ?? null,
            'meeting_date' => $data['meeting_date'] ?? null,
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);
    }

    public function updateMeeting(Meeting $meeting, array $data)
    {
        return $meeting->update($data);
    }

    public function publishMeeting(Meeting $meeting)
    {
        return $meeting->update([
            'status' => 'published',
            'published_at' => now()
        ]);
    }

    public function deleteMeeting(Meeting $meeting)
    {
        return $meeting->delete();
    }
}
