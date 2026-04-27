<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $role = $user->roles->first()?->name ?? '';

        $announcements = Announcement::with('creator')
            ->published()
            ->activePeriod()
            ->forRole($role)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Shared/Announcements/Index', [
            'announcements' => $announcements,
        ]);
    }
}
