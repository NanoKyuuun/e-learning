<?php

namespace App\Http\Controllers\Kajur;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kajur\StoreAnnouncementRequest;
use App\Http\Requests\Kajur\UpdateAnnouncementRequest;
use App\Models\Announcement;
use App\Services\Kajur\AnnouncementService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AnnouncementController extends Controller
{
    protected AnnouncementService $announcementService;

    public function __construct(AnnouncementService $announcementService)
    {
        $this->announcementService = $announcementService;
    }

    public function index(Request $request)
    {
        return Inertia::render('Kajur/Announcements/Index', [
            'announcements' => $this->announcementService->getAllAnnouncements($request->input('search')),
            'filters'       => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Kajur/Announcements/Create');
    }

    public function store(StoreAnnouncementRequest $request)
    {
        $this->announcementService->createAnnouncement($request->validated());

        return redirect()->route('kajur.announcements.index')
            ->with('success', 'Pengumuman berhasil dibuat.');
    }

    public function edit(Announcement $announcement)
    {
        return Inertia::render('Kajur/Announcements/Edit', [
            'announcement' => $announcement,
        ]);
    }

    public function update(UpdateAnnouncementRequest $request, Announcement $announcement)
    {
        $this->announcementService->updateAnnouncement($announcement, $request->validated());

        return redirect()->route('kajur.announcements.index')
            ->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Announcement $announcement)
    {
        $this->announcementService->deleteAnnouncement($announcement);

        return redirect()->route('kajur.announcements.index')
            ->with('success', 'Pengumuman berhasil dihapus.');
    }
}
