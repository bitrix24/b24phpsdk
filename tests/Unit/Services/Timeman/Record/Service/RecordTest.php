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

namespace Bitrix24\SDK\Tests\Unit\Services\Timeman\Record\Service;

use Bitrix24\SDK\Services\Timeman\Record\Result\RecordsResult;
use Bitrix24\SDK\Services\Timeman\Record\Service\Record;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Record::class)]
class RecordTest extends TestCase
{
    private Record $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = new Record(new NullCore(), new NullLogger());
    }

    #[Test]
    public function testListReturnsRecordsResult(): void
    {
        $this->assertInstanceOf(
            RecordsResult::class,
            $this->service->list()
        );
    }
}
