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
use Bitrix24\SDK\Services\Main\Result\EventLogResult;
use Bitrix24\SDK\Services\Main\Service\EventLogSelectBuilder;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EventLogItemResult::class)]
class EventLogItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    public function testAllFieldsAreAnnotated(): void
    {
        $rawItem = $this->getSample()->getCoreResponse()->getResponseData()->getResult()['item'];
        $this->assertBitrix24AllResultItemFieldsAnnotated(array_keys($rawItem), EventLogItemResult::class);
    }

    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations($this->getSample()->eventLogItem(), EventLogItemResult::class);
    }

    private function getSample(): EventLogResult
    {
        $eventLog = Factory::getServiceBuilder()->getMainScope()->eventLog();
        $items = $eventLog->list(['id'], pagination: ['limit' => 1])->getEventLogItems();
        if ($items === []) {
            self::markTestSkipped('No event log entries available for live result-item validation.');
        }

        return $eventLog->get($items[0]->id, (new EventLogSelectBuilder())->allSystemFields());
    }
}
