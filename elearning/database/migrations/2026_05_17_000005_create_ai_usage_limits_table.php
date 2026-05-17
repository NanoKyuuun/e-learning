<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_usage_limits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('role', 30)->unique(); // admin-sistem, kajur, guru, siswa
            $table->unsignedInteger('daily_chat_limit')->default(20);
            $table->unsignedInteger('daily_web_search_limit')->default(10);
            $table->unsignedInteger('daily_document_process_limit')->default(5);
            $table->unsignedInteger('max_file_size_mb')->default(20);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_usage_limits');
    }
};
