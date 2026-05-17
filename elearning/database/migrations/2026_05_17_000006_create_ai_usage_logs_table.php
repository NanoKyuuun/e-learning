<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_usage_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->index();
            $table->string('feature', 30); // chat, summary, quiz, glossary, web_search, parse_document
            $table->uuid('meeting_id')->nullable()->index();
            $table->uuid('ai_document_id')->nullable()->index();
            $table->string('model')->nullable();
            $table->unsignedInteger('web_search_requests')->nullable();
            $table->string('status', 10)->default('success'); // success, failed
            $table->text('error_message')->nullable();
            $table->unsignedInteger('latency_ms')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_usage_logs');
    }
};
