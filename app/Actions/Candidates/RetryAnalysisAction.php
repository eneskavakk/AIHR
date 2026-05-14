<?php

namespace App\Actions\Candidates;

use App\Enums\AnalysisStatus;
use App\Jobs\ProcessCandidateAnalysisJob;
use App\Models\CandidateAnalysis;
use Illuminate\Support\Facades\Log;

final class RetryAnalysisAction
{
    /**
     * Safely transition a failed analysis back to pending and re-dispatch.
     *
     * Returns true if retry was dispatched, false if transition was invalid.
     */
    public function handle(CandidateAnalysis $candidateAnalysis): bool
    {
        if ($candidateAnalysis->status !== AnalysisStatus::Failed) {
            Log::info('RetryAnalysisAction: only failed analyses can be retried.', [
                'candidate_analysis_id' => $candidateAnalysis->id,
                'current_status' => $candidateAnalysis->status->value,
            ]);

            return false;
        }

        $candidateAnalysis->forceFill([
            'status' => AnalysisStatus::Pending,
            'error_message' => null,
            'score' => null,
            'candidate_level' => null,
            'result_json' => null,
            'raw_ai_response' => null,
            'started_at' => null,
            'completed_at' => null,
        ])->save();

        ProcessCandidateAnalysisJob::dispatch($candidateAnalysis->id);

        Log::info('RetryAnalysisAction: analysis queued for retry.', [
            'candidate_analysis_id' => $candidateAnalysis->id,
        ]);

        return true;
    }
}
