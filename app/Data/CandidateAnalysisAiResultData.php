<?php

namespace App\Data;

final readonly class CandidateAnalysisAiResultData
{
    /**
     * @param  array<string, mixed>  $result
     */
    public function __construct(
        public array $result,
        public string $rawResponse,
        public string $model,
        public int $attemptCount,
        public string $promptVersion,
    ) {}

    /**
     * @param  array{result: array<string, mixed>, raw_response: string, model: string, attempt_count: int, prompt_version: string}  $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            result: $payload['result'],
            rawResponse: $payload['raw_response'],
            model: $payload['model'],
            attemptCount: (int) $payload['attempt_count'],
            promptVersion: $payload['prompt_version'],
        );
    }
}
