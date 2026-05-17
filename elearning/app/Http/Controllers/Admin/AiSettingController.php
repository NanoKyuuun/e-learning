<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ai\AiUsageLimit;
use App\Services\Ai\AiGatewayService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AiSettingController extends Controller
{
    public function __construct(private AiGatewayService $gateway) {}

    /**
     * Halaman pengaturan AI.
     * GET /admin/ai/settings
     */
    public function index()
    {
        $health = $this->gateway->healthCheck();
        $limits = AiUsageLimit::all()->keyBy('role');

        return Inertia::render('Admin/Ai/Settings', [
            'health' => $health,
            'limits' => $limits,
            'config' => [
                'ai_service_url'    => config('services.ai_service.url'),
                'openrouter_model'  => config('services.openrouter.model'),
                'web_search_mode'   => config('services.openrouter.web_search.mode'),
                'web_search_enabled'=> config('services.ai_service.web_search_enabled', true),
            ],
        ]);
    }

    /**
     * Update limit penggunaan AI.
     * PATCH /admin/ai/settings
     */
    public function update(Request $request)
    {
        $request->validate([
            'limits'                         => 'required|array',
            'limits.*.role'                  => 'required|string',
            'limits.*.daily_chat_limit'      => 'required|integer|min:0|max:1000',
            'limits.*.daily_web_search_limit'=> 'required|integer|min:0|max:500',
            'limits.*.daily_document_process_limit' => 'required|integer|min:0|max:100',
            'limits.*.max_file_size_mb'      => 'required|integer|min:1|max:100',
            'limits.*.is_active'             => 'required|boolean',
        ]);

        foreach ($request->limits as $limitData) {
            AiUsageLimit::updateOrCreate(
                ['role' => $limitData['role']],
                $limitData
            );
        }

        return back()->with('success', 'Konfigurasi AI berhasil disimpan.');
    }

    /**
     * Health check real-time ke Python AI service.
     * GET /admin/ai/health
     */
    public function health()
    {
        return response()->json($this->gateway->healthCheck());
    }
}
