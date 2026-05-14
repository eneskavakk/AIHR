<?php

namespace App\Services\Candidates;

use App\Enums\AnalysisStatus;
use App\Models\CandidateAnalysis;
use App\Models\CandidateCv;

final class EnsureCandidateAnalysis
{
    public function handle(CandidateCv $candidateCv): CandidateAnalysis
    {
        return CandidateAnalysis::query()->firstOrCreate(
            ['candidate_cv_id' => $candidateCv->id],
            [
                'job_posting_id' => $candidateCv->job_posting_id,
                'status' => AnalysisStatus::Pending,
            ],
        );
    }
}
