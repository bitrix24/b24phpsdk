<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\OpenApi\Domain\ResultItem\Path;

use Bitrix24\SDK\OpenApi\Domain\ResultItem\Path\ResultItemTaskPathResolver;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ResultItemTaskPathResolverTest extends TestCase
{
    #[Test]
    public function itBuildsTaskArtifactPaths(): void
    {
        $resultItemTaskPathResolver = new ResultItemTaskPathResolver();

        self::assertSame(
            '.tasks/425/im.dialog.get/result-item.payload.yaml',
            $resultItemTaskPathResolver->payloadPath('425', 'im.dialog.get')
        );
        self::assertSame(
            '.tasks/425/im.dialog.get/result-item.verification-report.yaml',
            $resultItemTaskPathResolver->verificationReportPath('425', 'im.dialog.get')
        );
    }
}
