<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Infrastructure\Console\Commands\Generator;

use Bitrix24\SDK\Infrastructure\Console\Commands\Generator\BranchIssueIdResolver;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class BranchIssueIdResolverTest extends TestCase
{
    #[Test]
    public function itExtractsIssueIdFromFeatureBranch(): void
    {
        self::assertSame('425', (new BranchIssueIdResolver())->resolve('feature/425-add-im-dialog-service'));
    }

    #[Test]
    public function itExtractsIssueIdFromBugfixBranch(): void
    {
        self::assertSame('512', (new BranchIssueIdResolver())->resolve('bugfix/512-fix-generator'));
    }

    #[Test]
    public function itRejectsUnsupportedBranchNames(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected feature/<id>-... or bugfix/<id>-...');

        (new BranchIssueIdResolver())->resolve('main');
    }
}
