<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Meeting;
use App\Services\Guru\MaterialService;
use App\Http\Requests\Guru\StoreMaterialRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    use AuthorizesRequests;

    protected $materialService;

    public function __construct(MaterialService $materialService)
    {
        $this->materialService = $materialService;
    }

    public function store(StoreMaterialRequest $request, Meeting $meeting)
    {
        $this->authorize('update', $meeting);

        $this->materialService->createMaterial($meeting, $request->validated());

        return redirect()->back()->with('success', 'Materi berhasil diunggah.');
    }

    public function destroy(Material $material)
    {
        $this->authorize('delete', $material);

        $this->materialService->deleteMaterial($material);
        
        return redirect()->back()->with('success', 'Materi berhasil dihapus.');
    }
}
