<?php

namespace App\Observers;

use App\Jobs\ProcessCandidateAnalysisJob;
use App\Models\CandidateCv;
use App\Services\Candidates\EnsureCandidateAnalysis;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

final readonly class CandidateCvObserver
{
    public function __construct(
        private EnsureCandidateAnalysis $ensureCandidateAnalysis,
    ) {}

    public function creating(CandidateCv $candidateCv): void
    {
        $candidateCv->uploaded_by ??= auth()->id();

        if ($candidateCv->stored_file_path !== null) {
            $disk = Storage::disk('local');

            if ($disk->exists($candidateCv->stored_file_path)) {
                $candidateCv->mime_type = $candidateCv->mime_type ?: ($disk->mimeType($candidateCv->stored_file_path) ?: 'application/pdf');
                $candidateCv->file_size = $candidateCv->file_size ?: $disk->size($candidateCv->stored_file_path);
            }
        }

        $candidateCv->mime_type = $candidateCv->mime_type ?: 'application/pdf';
        $candidateCv->file_size = $candidateCv->file_size ?: 0;
    }

    public function created(CandidateCv $candidateCv): void
    {
        try {
            $analysis = $this->ensureCandidateAnalysis->handle($candidateCv);

            // Dispatch with 5 second delay so the file upload fully completes
            ProcessCandidateAnalysisJob::dispatch($analysis->id)->delay(now()->addSeconds(5));
        } catch (Throwable $e) {
            // Don't crash the CV creation if queue dispatch fails
            // The analysis can be triggered manually later
            Log::warning('Auto-dispatch failed after CV creation. Analysis can be started manually.', [
                'candidate_cv_id' => $candidateCv->id,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
