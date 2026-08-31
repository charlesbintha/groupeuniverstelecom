<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();

            $table->string('document_type', 50);
            $table->foreignId('deliverable_id')->nullable()->constrained('project_deliverables')->cascadeOnDelete();

            $table->string('name');
            $table->string('original_filename');
            $table->string('stored_filename');
            $table->string('path');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size');

            $table->string('contract_type', 50)->nullable();

            $table->foreignId('uploaded_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_id', 'document_type'], 'idx_project_documents_project_type');
            $table->index('deliverable_id', 'idx_project_documents_deliverable');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_documents');
    }
};
