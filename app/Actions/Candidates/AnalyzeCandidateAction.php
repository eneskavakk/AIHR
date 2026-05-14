<?php

namespace App\Actions\Candidates;

use App\Enums\AnalysisStatus;
use App\Enums\CandidateLevel;
use App\Models\CandidateAnalysis;
use App\Services\Ai\AiServiceClient;
use Illuminate\Support\Facades\Log;
use Throwable;

final readonly class AnalyzeCandidateAction
{
    public function __construct(
        private AiServiceClient $aiServiceClient,
    ) {}

    public function handle(CandidateAnalysis $candidateAnalysis): bool
    {
        // Guard: never re-process a completed analysis
        if ($candidateAnalysis->status === AnalysisStatus::Completed) {
            Log::info('AnalyzeCandidateAction: skipping completed analysis.', [
                'candidate_analysis_id' => $candidateAnalysis->id,
            ]);

            return false;
        }

        // Guard: do not start if already processing (idempotency)
        if ($candidateAnalysis->status === AnalysisStatus::Processing) {
            Log::info('AnalyzeCandidateAction: skipping already-processing analysis.', [
                'candidate_analysis_id' => $candidateAnalysis->id,
            ]);

            return false;
        }

        $candidateAnalysis->loadMissing(['jobPosting', 'candidateCv']);

        if (blank($candidateAnalysis->candidateCv->cleaned_text)) {
            $candidateAnalysis->forceFill([
                'status' => AnalysisStatus::Failed,
                'error_message' => 'CV temizlenmiş metni bulunamadı. Önce PDF metnini çıkarın.',
            ])->save();

            return false;
        }

        $candidateAnalysis->forceFill([
            'status' => AnalysisStatus::Processing,
            'error_message' => null,
            'started_at' => now(),
            'completed_at' => null,
        ])->save();

        try {
            $result = $this->aiServiceClient->analyzeCandidate($candidateAnalysis);
            $score = (int) $result->result['uygunluk_skoru'];

            $candidateAnalysis->forceFill([
                'status' => AnalysisStatus::Completed,
                'score' => $score,
                'candidate_level' => $this->levelForScore($score),
                'result_json' => $result->result,
                'raw_ai_response' => $result->rawResponse,
                'error_message' => null,
                'completed_at' => now(),
            ])->save();

            return true;
        } catch (Throwable $exception) {
            $candidateAnalysis->forceFill([
                'status' => AnalysisStatus::Failed,
                'error_message' => 'AI analiz çıktısı doğrulanamadı veya servis yanıt vermedi.',
                'completed_at' => now(),
            ])->save();

            Log::warning('Candidate analysis failed.', [
                'candidate_analysis_id' => $candidateAnalysis->id,
                'message' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    private function levelForScore(int $score): CandidateLevel
    {
        return match (true) {
            $score <= 39 => CandidateLevel::WeakMatch,
            $score <= 59 => CandidateLevel::PartialMatch,
            $score <= 79 => CandidateLevel::StrongMatch,
            default => CandidateLevel::ExcellentMatch,
        };
    }
}
