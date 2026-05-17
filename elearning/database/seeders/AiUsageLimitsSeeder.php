<?php

namespace Database\Seeders;

use App\Models\Ai\AiUsageLimit;
use Illuminate\Database\Seeder;

class AiUsageLimitsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            [
                'role'                          => 'siswa',
                'daily_chat_limit'              => 20,
                'daily_web_search_limit'        => 10,
                'daily_document_process_limit'  => 0,   // siswa tidak bisa proses dokumen
                'max_file_size_mb'              => 0,
                'is_active'                     => true,
            ],
            [
                'role'                          => 'guru',
                'daily_chat_limit'              => 50,
                'daily_web_search_limit'        => 20,
                'daily_document_process_limit'  => 10,
                'max_file_size_mb'              => 20,
                'is_active'                     => true,
            ],
            [
                'role'                          => 'kajur',
                'daily_chat_limit'              => 30,
                'daily_web_search_limit'        => 15,
                'daily_document_process_limit'  => 5,
                'max_file_size_mb'              => 10,
                'is_active'                     => true,
            ],
            [
                'role'                          => 'admin-sistem',
                'daily_chat_limit'              => 100,
                'daily_web_search_limit'        => 50,
                'daily_document_process_limit'  => 20,
                'max_file_size_mb'              => 50,
                'is_active'                     => true,
            ],
        ];

        foreach ($defaults as $data) {
            AiUsageLimit::updateOrCreate(
                ['role' => $data['role']],
                $data
            );
        }

        $this->command->info('AI Usage Limits seeded successfully.');
    }
}
