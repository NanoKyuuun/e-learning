<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('material_id')->nullable()->index();
            $table->uuid('assignment_id')->nullable()->index();
            $table->uuid('meeting_id')->index();
            $table->uuid('teaching_assignment_id')->index();
            $table->uuid('uploaded_by')->index();
            $table->string('title');
            $table->string('original_filename');
            $table->text('file_path');
            $table->string('mime_type')->nullable();
            $table->string('file_extension', 10);
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('sha256_hash', 64)->nullable()->index();
            $table->string('processing_status', 20)->default('pending'); // pending, processing, completed, failed
            $table->text('error_message')->nullable();
            $table->unsignedInteger('total_pages')->nullable();
            $table->unsignedInteger('total_sheets')->nullable();
            $table->unsignedInteger('total_chunks')->default(0);
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_documents');
    }
};
