<?php

namespace Database\Factories;

use App\Enums\AnalysisStatus;
use App\Models\CandidateAnalysis;
use App\Models\CandidateCv;
use App\Models\JobPosting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CandidateAnalysis>
 */
class CandidateAnalysisFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $candidateCv = CandidateCv::factory();

        return [
            'job_posting_id' => JobPosting::factory(),
            'candidate_cv_id' => $candidateCv,
            'status' => AnalysisStatus::Pending,
            'score' => null,
            'candidate_level' => null,
            'result_json' => null,
            'raw_ai_response' => null,
            'error_message' => null,
            'started_at' => null,
            'completed_at' => null,
        ];
    }
}
