<?php

namespace App\Actions\Candidates;

use App\Enums\ParseStatus;
use App\Models\CandidateCv;
use App\Services\Ai\AiServiceClient;
use Illuminate\Support\Facades\Log;
use Throwable;

final readonly class ParseCandidateCvAction
{
    public function __construct(
        private AiServiceClient $aiServiceClient,
    ) {}

    public function handle(CandidateCv $candidateCv): bool
    {
        $candidateCv->forceFill([
            'parse_status' => ParseStatus::Pending,
        ])->save();

        try {
            $result = $this->aiServiceClient->parseCandidateCv($candidateCv);

            $candidateCv->forceFill([
                'raw_extracted_text' => $result->rawText,
                'cleaned_text' => $result->cleanedText,
                'parse_status' => ParseStatus::Completed,
            ])->save();

            if ($result->warnings !== []) {
                Log::info('CV parsed with warnings.', [
                    'candidate_cv_id' => $candidateCv->id,
                    'warnings' => $result->warnings,
                ]);
            }

            return true;
        } catch (Throwable $exception) {
            $candidateCv->forceFill([
                'parse_status' => ParseStatus::Failed,
            ])->save();

            Log::warning('CV parsing failed.', [
                'candidate_cv_id' => $candidateCv->id,
                'message' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}
