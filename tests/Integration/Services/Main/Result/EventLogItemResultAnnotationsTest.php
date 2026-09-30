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

namespace Bitrix24\SDK\Tests\Integration\Services\Main\Result;

use Bitrix24\SDK\Services\Main\Result\EventLogItemResult;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EventLogItemResult::class)]
class EventLogItemResultAnnotationsTest extends TestCase
{
    use CustomBitrix24Assertions;

    public function testAllSystemFieldsAnnotated(): void
    {
        $fields = Factory::getServiceBuilder()->getMainScope()->eventLogField()->list()->getFieldsDescription();
        self::assertNotEmpty($fields);
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($fields), EventLogItemResult::class);
    }

    public function testAllSystemFieldsHasValidTypeAnnotation(): void
    {
        $fields = Factory::getServiceBuilder()->getMainScope()->eventLogField()->list()->getFieldsDescription();
        self::assertNotEmpty($fields);
        // Live REST v3 reports object for this date-time DTO property; OpenAPI specifies string/date-time.
        self::assertContains($fields['timestampX']['type'], ['object', 'string', 'datetime']);
        $fields['timestampX']['type'] = 'datetime';
        $this->assertBitrix24AllResultItemFieldsHasValidTypeAnnotation($fields, EventLogItemResult::class);
    }
}
