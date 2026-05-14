<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('candidate_cvs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_posting_id')->constrained()->cascadeOnDelete();
            $table->string('candidate_name')->nullable();
            $table->string('candidate_email')->nullable();
            $table->string('original_file_name');
            $table->string('stored_file_path');
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size');
            $table->longText('raw_extracted_text')->nullable();
            $table->longText('cleaned_text')->nullable();
            $table->string('parse_status')->default('pending');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['job_posting_id', 'parse_status']);
            $table->index('candidate_name');
            $table->index('candidate_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidate_cvs');
    }
};
