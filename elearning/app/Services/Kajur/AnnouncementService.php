<?php

namespace App\Services\Kajur;

use App\Models\Announcement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AnnouncementService
{
    public function getAllAnnouncements(?string $search = null): LengthAwarePaginator
    {
        return Announcement::with('creator')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('body', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();
    }

    public function createAnnouncement(array $data): Announcement
    {
        $data['created_by'] = auth()->id();

        return Announcement::create($data);
    }

    public function updateAnnouncement(Announcement $announcement, array $data): bool
    {
        return $announcement->update($data);
    }

    public function deleteAnnouncement(Announcement $announcement): bool
    {
        return $announcement->delete();
    }
}
