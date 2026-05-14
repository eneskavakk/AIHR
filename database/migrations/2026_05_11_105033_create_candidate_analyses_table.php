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
        Schema::create('candidate_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_posting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('candidate_cv_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->unsignedTinyInteger('score')->nullable();
            $table->string('candidate_level')->nullable();
            $table->json('result_json')->nullable();
            $table->longText('raw_ai_response')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('candidate_cv_id');
            $table->index(['job_posting_id', 'status']);
            $table->index(['score', 'candidate_level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidate_analyses');
    }
};
