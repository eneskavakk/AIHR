<?php

namespace App\Data;

final readonly class CvParseResultData
{
    /**
     * @param  list<string>  $warnings
     */
    public function __construct(
        public string $rawText,
        public string $cleanedText,
        public int $pageCount,
        public array $warnings = [],
    ) {}

    /**
     * @param  array{raw_text: string, cleaned_text: string, page_count: int, warnings?: list<string>}  $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            rawText: $payload['raw_text'],
            cleanedText: $payload['cleaned_text'],
            pageCount: (int) $payload['page_count'],
            warnings: $payload['warnings'] ?? [],
        );
    }
}
