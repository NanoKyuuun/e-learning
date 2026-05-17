<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_generated_outputs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->index();
            $table->uuid('meeting_id')->index();
            $table->uuid('ai_document_id')->nullable()->index();
            $table->string('output_type', 30); // summary, quiz, glossary, key_points, discussion_questions
            $table->string('title')->nullable();
            $table->json('content_json');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_generated_outputs');
    }
};
