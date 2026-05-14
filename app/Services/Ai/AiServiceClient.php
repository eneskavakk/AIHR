<?php

namespace App\Services\Ai;

use App\Data\AiServiceHealthData;
use App\Data\CandidateAnalysisAiResultData;
use App\Data\CvParseResultData;
use App\Models\CandidateAnalysis;
use App\Models\CandidateCv;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

final class AiServiceClient
{
    public function health(): AiServiceHealthData
    {
        $response = $this->http()->get('/health');

        $response->throw();

        return AiServiceHealthData::fromArray($response->json());
    }

    public function parseCandidateCv(CandidateCv $candidateCv): CvParseResultData
    {
        $disk = Storage::disk('local');

        $response = $this->http()
            ->attach(
                'file',
                $disk->get($candidateCv->stored_file_path),
                $candidateCv->original_file_name,
                ['Content-Type' => 'application/pdf'],
            )
            ->post('/parse-cv');

        $response->throw();

        return CvParseResultData::fromArray($response->json());
    }

    public function analyzeCandidate(CandidateAnalysis $candidateAnalysis): CandidateAnalysisAiResultData
    {
        $candidateAnalysis->loadMissing(['jobPosting', 'candidateCv']);

        $response = $this->http()
            ->post('/analyze-candidate', [
                'job_posting' => [
                    'title' => $candidateAnalysis->jobPosting->title,
                    'description' => $candidateAnalysis->jobPosting->description,
                    'requirements' => $candidateAnalysis->jobPosting->requirements,
                    'responsibilities' => $candidateAnalysis->jobPosting->responsibilities,
                    'seniority_level' => $candidateAnalysis->jobPosting->seniority_level,
                ],
                'candidate' => [
                    'cleaned_text' => $candidateAnalysis->candidateCv->cleaned_text,
                ],
                'language_hint' => $candidateAnalysis->jobPosting->language,
            ]);

        $response->throw();

        return CandidateAnalysisAiResultData::fromArray($response->json());
    }

    private function http(): PendingRequest
    {
        $request = Http::baseUrl(config('aihr.ai_service_url'))
            ->acceptJson()
            ->timeout((int) config('aihr.ai_service_timeout'));

        $token = config('aihr.ai_service_token');

        if ($token) {
            $request->withToken($token);
        }

        return $request;
    }
}
