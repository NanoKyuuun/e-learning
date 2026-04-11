<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\TeachingAssignment;
use App\Services\Guru\MeetingService;
use App\Http\Requests\Guru\StoreMeetingRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MeetingController extends Controller
{
    use AuthorizesRequests;

    protected $meetingService;

    public function __construct(MeetingService $meetingService)
    {
        $this->meetingService = $meetingService;
    }

    public function index(TeachingAssignment $teachingAssignment)
    {
        $this->authorize('view', $teachingAssignment);

        return Inertia::render('Guru/Meetings/Index', [
            'teachingAssignment' => $teachingAssignment->load(['classGroup', 'subject']),
            'meetings' => $this->meetingService->getMeetingsByAssignment($teachingAssignment),
        ]);
    }

    public function store(StoreMeetingRequest $request, TeachingAssignment $teachingAssignment)
    {
        $this->authorize('view', $teachingAssignment);

        $this->meetingService->createMeeting($teachingAssignment, $request->validated());

        return redirect()->back()->with('success', 'Pertemuan berhasil dibuat.');
    }

    public function show(Meeting $meeting)
    {
        $this->authorize('view', $meeting);

        return Inertia::render('Guru/Meetings/Show', [
            'meeting' => $meeting->load([
                'teachingAssignment.classGroup', 
                'teachingAssignment.subject', 
                'materials', 
                'assignments' => function($query) {
                    $query->withCount('submissions');
                }
            ]),
        ]);
    }

    public function publish(Meeting $meeting)
    {
        $this->authorize('update', $meeting);
        $this->meetingService->publishMeeting($meeting);
        return redirect()->back()->with('success', 'Pertemuan berhasil dipublikasikan.');
    }

    public function destroy(Meeting $meeting)
    {
        $this->authorize('delete', $meeting);
        $this->meetingService->deleteMeeting($meeting);
        return redirect()->back()->with('success', 'Pertemuan berhasil dihapus.');
    }
}
