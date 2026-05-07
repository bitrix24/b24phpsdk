<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\Main\EventLogField\Service;

use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Services\Main\EventLogField\Result\EventLogFieldResult;
use Bitrix24\SDK\Services\Main\EventLogField\Result\EventLogFieldsResult;
use Bitrix24\SDK\Services\Main\EventLogField\Service\EventLogField;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(EventLogField::class)]
class EventLogFieldTest extends TestCase
{
    private EventLogField $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = new EventLogField(new NullCore(), new NullLogger());
    }

    #[Test]
    public function testGetReturnsEventLogFieldResult(): void
    {
        $this->assertInstanceOf(
            EventLogFieldResult::class,
            $this->service->get('timestampX')
        );
    }

    #[Test]
    public function testListReturnsEventLogFieldsResult(): void
    {
        $this->assertInstanceOf(
            EventLogFieldsResult::class,
            $this->service->list()
        );
    }

    #[Test]
    public function testGetThrowsOnEmptyName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        /** @phpstan-ignore argument.type */
        $this->service->get('');
    }
}
