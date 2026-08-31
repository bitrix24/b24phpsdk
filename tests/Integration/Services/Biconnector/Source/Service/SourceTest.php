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

namespace Bitrix24\SDK\Tests\Integration\Services\Biconnector\Source\Service;

use Bitrix24\SDK\Services\Biconnector\Source\Service\Source;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Source::class)]
class SourceTest extends TestCase
{
    private const SKIP_REASON = 'This test requires an additional external service (a real database accessible via the Biconnector connector).';

    #[\Override]
    protected function setUp(): void
    {
        $this->markTestSkipped(self::SKIP_REASON);
    }

    public function testAdd(): void
    {
    }

    public function testGet(): void
    {
    }

    public function testList(): void
    {
    }

    public function testUpdate(): void
    {
    }

    public function testDelete(): void
    {
    }

    public function testFields(): void
    {
    }

    public function testCount(): void
    {
    }
}
