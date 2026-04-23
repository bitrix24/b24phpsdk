<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\OpenApi\Domain;

use Bitrix24\SDK\OpenApi\Domain\ResultItem\ResultItemTaskPathResolver;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ResultItemTaskPathResolverTest extends TestCase
{
    #[Test]
    public function itBuildsTaskArtifactPaths(): void
    {
        $resolver = new ResultItemTaskPathResolver();

        self::assertSame(
            '.tasks/425/im.dialog.get/result-item.payload.yaml',
            $resolver->payloadPath('425', 'im.dialog.get')
        );
        self::assertSame(
            '.tasks/425/im.dialog.get/result-item.verification-report.yaml',
            $resolver->verificationReportPath('425', 'im.dialog.get')
        );
    }
}
