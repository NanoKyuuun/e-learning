<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_chat_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('session_id')->index();
            $table->string('sender', 20); // user, assistant
            $table->longText('message');
            $table->json('sources_json')->nullable();          // sumber dokumen atau link internet
            $table->json('server_tool_usage_json')->nullable(); // info pemakaian server tool
            $table->string('model')->nullable();
            $table->unsignedInteger('prompt_tokens')->nullable();
            $table->unsignedInteger('completion_tokens')->nullable();
            $table->unsignedInteger('latency_ms')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign('session_id')
                ->references('id')
                ->on('ai_chat_sessions')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_chat_messages');
    }
};
