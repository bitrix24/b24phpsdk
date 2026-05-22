<?php

declare(strict_types=1);

namespace Bitrix24\SDK\OpenApi\Domain\ResultItem\Verification;

final readonly class ResultItemVerificationReport
{
    /**
     * @param list<array<string, mixed>> $confirmedFields
     * @param list<array<string, mixed>> $missingFields
     * @param list<array<string, mixed>> $unexpectedFields
     * @param list<array<string, mixed>> $typeMismatches
     * @param list<array<string, mixed>> $nullabilityObservations
     */
    public function __construct(
        public string $method,
        public array $confirmedFields,
        public array $missingFields,
        public array $unexpectedFields,
        public array $typeMismatches,
        public array $nullabilityObservations,
    ) {
    }
}
