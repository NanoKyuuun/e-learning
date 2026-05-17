<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_document_chunks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('ai_document_id')->index();
            $table->unsignedInteger('chunk_index');
            $table->unsignedInteger('page_number')->nullable();
            $table->string('sheet_name')->nullable();
            $table->string('heading')->nullable();
            $table->longText('content');
            $table->unsignedInteger('token_estimate')->nullable();
            $table->json('embedding')->nullable(); // untuk local embedding di masa depan
            $table->timestamps();

            $table->foreign('ai_document_id')
                ->references('id')
                ->on('ai_documents')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_document_chunks');
    }
};
