<?php

declare(strict_types=1);

namespace Bitrix24\SDK\OpenApi\Domain\ResultItem\Path;

readonly class ResultItemTaskPathResolver
{
    public function __construct(
        private string $tasksRoot = '.tasks',
    ) {
    }

    public function payloadPath(string $issueId, string $methodName): string
    {
        return sprintf(
            '%s/%s/%s/result-item.payload.yaml',
            rtrim($this->tasksRoot, '/'),
            $issueId,
            $methodName
        );
    }

    public function verificationReportPath(string $issueId, string $methodName): string
    {
        return sprintf(
            '%s/%s/%s/result-item.verification-report.yaml',
            rtrim($this->tasksRoot, '/'),
            $issueId,
            $methodName
        );
    }
}
