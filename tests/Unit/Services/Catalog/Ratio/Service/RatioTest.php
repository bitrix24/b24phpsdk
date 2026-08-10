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

namespace Bitrix24\SDK\Tests\Unit\Services\Catalog\Ratio\Service;

use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Result\FieldsResult;
use Bitrix24\SDK\Services\Catalog\Ratio\Result\RatioResult;
use Bitrix24\SDK\Services\Catalog\Ratio\Result\RatiosResult;
use Bitrix24\SDK\Services\Catalog\Ratio\Service\Ratio;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Ratio::class)]
class RatioTest extends TestCase
{
    private Ratio $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = new Ratio(new NullCore(), new NullLogger());
    }

    #[Test]
    public function testGetReturnsRatioResult(): void
    {
        $this->assertInstanceOf(RatioResult::class, $this->service->get(1));
    }

    #[Test]
    public function testListReturnsRatiosResult(): void
    {
        $this->assertInstanceOf(RatiosResult::class, $this->service->list());
    }

    #[Test]
    public function testFieldsReturnsFieldsResult(): void
    {
        $this->assertInstanceOf(FieldsResult::class, $this->service->fields());
    }

    #[Test]
    public function testGetThrowsOnNonPositiveId(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->get(0);
    }
}
