<?php

namespace App\Data;

final readonly class AiServiceHealthData
{
    public function __construct(
        public bool $ok,
        public string $service,
        public ?string $version = null,
    ) {}

    /**
     * @param  array{ok?: bool, service?: string, version?: string|null}  $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            ok: (bool) ($payload['ok'] ?? false),
            service: (string) ($payload['service'] ?? 'ai-service'),
            version: $payload['version'] ?? null,
        );
    }
}
