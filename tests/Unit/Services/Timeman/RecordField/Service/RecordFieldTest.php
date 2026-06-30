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

namespace Bitrix24\SDK\Tests\Unit\Services\Timeman\RecordField\Service;

use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Services\Timeman\RecordField\Result\RecordFieldResult;
use Bitrix24\SDK\Services\Timeman\RecordField\Result\RecordFieldsResult;
use Bitrix24\SDK\Services\Timeman\RecordField\Service\RecordField;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(RecordField::class)]
class RecordFieldTest extends TestCase
{
    private RecordField $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = new RecordField(new NullCore(), new NullLogger());
    }

    #[Test]
    public function testGetReturnsRecordFieldResult(): void
    {
        $this->assertInstanceOf(
            RecordFieldResult::class,
            $this->service->get('startTime')
        );
    }

    #[Test]
    public function testListReturnsRecordFieldsResult(): void
    {
        $this->assertInstanceOf(
            RecordFieldsResult::class,
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
