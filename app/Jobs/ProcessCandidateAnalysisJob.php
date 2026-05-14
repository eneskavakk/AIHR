<?php

namespace App\Jobs;

use App\Actions\Candidates\AnalyzeCandidateAction;
use App\Actions\Candidates\ParseCandidateCvAction;
use App\Enums\AnalysisStatus;
use App\Models\CandidateAnalysis;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessCandidateAnalysisJob implements ShouldQueue
{
    use Queueable;

    /**
     * Retry up to 3 times with backoff: 30s, then 60s.
     */
    public int $tries = 3;

    /** @var int[] */
    public array $backoff = [30, 60];

    /**
     * LLM inference can take 30+ seconds; allow up to 5 minutes.
     */
    public int $timeout = 300;

    public function __construct(
        public int $candidateAnalysisId,
    ) {}

    public function handle(
        ParseCandidateCvAction $parseCv,
        AnalyzeCandidateAction $analyzeCandidate,
    ): void {
        $candidateAnalysis = CandidateAnalysis::query()->findOrFail($this->candidateAnalysisId);

        // Guard: do not re-process completed or already-processing analyses
        if ($candidateAnalysis->status === AnalysisStatus::Completed) {
            Log::info('ProcessCandidateAnalysisJob: skipping completed analysis.', [
                'candidate_analysis_id' => $candidateAnalysis->id,
            ]);

            return;
        }

        if ($candidateAnalysis->status === AnalysisStatus::Processing) {
            Log::info('ProcessCandidateAnalysisJob: skipping already-processing analysis.', [
                'candidate_analysis_id' => $candidateAnalysis->id,
            ]);

            return;
        }

        $candidateAnalysis->loadMissing('candidateCv');
        $cv = $candidateAnalysis->candidateCv;

        // Step 1: Parse CV if cleaned text is missing
        if (blank($cv->cleaned_text)) {
            $parsed = $parseCv->handle($cv);

            if (! $parsed) {
                $candidateAnalysis->forceFill([
                    'status' => AnalysisStatus::Failed,
                    'error_message' => 'PDF metni çıkarılamadı. Lütfen CV dosyasını kontrol edin.',
                    'completed_at' => now(),
                ])->save();

                return;
            }

            // Refresh to get updated cleaned_text
            $cv->refresh();
        }

        // Step 2: Run AI analysis
        $analyzeCandidate->handle($candidateAnalysis);
    }

    /**
     * Handle a job failure after all retries are exhausted.
     */
    public function failed(?\Throwable $exception): void
    {
        $candidateAnalysis = CandidateAnalysis::query()->find($this->candidateAnalysisId);

        if ($candidateAnalysis && $candidateAnalysis->status !== AnalysisStatus::Completed) {
            $candidateAnalysis->forceFill([
                'status' => AnalysisStatus::Failed,
                'error_message' => 'Analiz tüm denemelerden sonra başarısız oldu: '.($exception?->getMessage() ?? 'Bilinmeyen hata'),
                'completed_at' => now(),
            ])->save();
        }

        Log::error('ProcessCandidateAnalysisJob permanently failed.', [
            'candidate_analysis_id' => $this->candidateAnalysisId,
            'message' => $exception?->getMessage(),
        ]);
    }
}
