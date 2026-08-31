<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Biconnector\Source\Result;

use Bitrix24\SDK\Services\Biconnector\Source\Result\SourceItemResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(SourceItemResult::class)]
class SourceItemResultAnnotationsTest extends TestCase
{
    private const SKIP_REASON = 'This test requires an additional external service (a real database accessible via the Biconnector connector).';

    #[\Override]
    protected function setUp(): void
    {
        $this->markTestSkipped(self::SKIP_REASON);
    }

    #[Test]
    #[TestDox('all fields in SourceItemResult are annotated and match live API fields schema')]
    public function testAllSystemFieldsAnnotated(): void
    {
    }

    #[Test]
    #[TestDox('all fields in SourceItemResult have valid type casting matching API fields schema')]
    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
    }
}
