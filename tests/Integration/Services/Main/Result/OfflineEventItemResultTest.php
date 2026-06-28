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

use Bitrix24\SDK\Services\Main\Result\OfflineEventItemResult;
use Bitrix24\SDK\Services\Main\Service\Event;
use Bitrix24\SDK\Services\Main\Service\EventType;
use Bitrix24\SDK\Services\Main\Service\Main;
use Bitrix24\SDK\Services\Main\Service\OfflineEvent;
use Bitrix24\SDK\Services\Task\Events\OnTaskAdd\OnTaskAdd;
use Bitrix24\SDK\Services\Task\Service\Task;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Typhoon\Reflection\TyphoonReflector;

use function Typhoon\Type\stringify;

#[CoversClass(OfflineEventItemResult::class)]
class OfflineEventItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    private const string EVENT_CODE = OnTaskAdd::CODE;

    private const string HANDLER_URL = 'https://example.com/b24phpsdk-offline-handler';

    private OfflineEvent $offlineEventService;

    private Event $eventService;

    private Task $taskService;

    private Main $mainService;

    private int $createdTaskId = 0;

    #[\Override]
    protected function setUp(): void
    {
        $serviceBuilder = Factory::getServiceBuilder(true);
        $this->offlineEventService = $serviceBuilder->getMainScope()->offlineEvent();
        $this->eventService = $serviceBuilder->getMainScope()->event();
        $this->mainService = $serviceBuilder->getMainScope()->main();
        $this->taskService = $serviceBuilder->getTaskScope()->task();

        // ensure at least one offline event is queued so the result item carries real data
        $this->eventService->bind(self::EVENT_CODE, self::HANDLER_URL, eventType: EventType::offline);
        $responsibleId = $this->mainService->getCurrentUserProfile()->getUserProfile()->ID;
        $this->createdTaskId = $this->taskService->add([
            'TITLE' => 'b24phpsdk offline-event annotations test',
            'RESPONSIBLE_ID' => $responsibleId,
        ])->task()->id;
        $this->waitForEvent();
    }

    #[\Override]
    protected function tearDown(): void
    {
        try {
            $this->offlineEventService->get(['=EVENT_NAME' => self::EVENT_CODE], null, true);
        } catch (\Throwable) {
        }

        if ($this->createdTaskId > 0) {
            try {
                $this->taskService->delete($this->createdTaskId);
            } catch (\Throwable) {
            }
        }

        try {
            $this->eventService->unbind(self::EVENT_CODE, self::HANDLER_URL, eventType: EventType::offline);
        } catch (\Throwable) {
        }
    }

    #[Test]
    #[TestDox('all fields in OfflineEventItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllFieldsAreAnnotated(): void
    {
        $rawEvents = $this->offlineEventService->list(['=EVENT_NAME' => self::EVENT_CODE], ['ID' => 'DESC'])
            ->getCoreResponse()->getResponseData()->getResult();

        $this->assertNotEmpty($rawEvents, 'offline queue is empty, cannot validate annotations');

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            array_keys($rawEvents[0]),
            OfflineEventItemResult::class
        );
    }

    #[Test]
    #[TestDox('all fields in OfflineEventItemResult have valid type casting in magic getters')]
    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $events = $this->offlineEventService->list(['=EVENT_NAME' => self::EVENT_CODE], ['ID' => 'DESC'])->getEvents();
        $this->assertNotEmpty($events, 'offline queue is empty, cannot validate type casting');

        $offlineEventItemResult = $events[0];

        // type-cast check is done inline (not via assertBitrix24ResultItemFieldsTypeCastMatchAnnotations)
        // because that shared assertion passes the raw union-type string (e.g. "Carbon\CarbonImmutable|null")
        // to assertInstanceOf(), which PHP rejects as an invalid class name.
        $properties = TyphoonReflector::build()
            ->reflectClass(OfflineEventItemResult::class)
            ->properties();

        foreach ($properties as $property) {
            if (!$property->isAnnotated()) {
                continue;
            }

            if ($property->isNative()) {
                continue;
            }

            $propName = $property->id->name;
            $typeStr = stringify($property->type());
            $value = $offlineEventItemResult->$propName;

            if (str_contains($typeStr, 'null') && $value === null) {
                continue;
            }

            $message = sprintf(
                'field «%s» in «%s» annotated as «%s» but actual PHP type is «%s»',
                $propName,
                OfflineEventItemResult::class,
                $typeStr,
                get_debug_type($value)
            );

            match (true) {
                str_contains($typeStr, CarbonImmutable::class) => $this->assertInstanceOf(CarbonImmutable::class, $value, $message),
                str_contains($typeStr, 'array') => $this->assertIsArray($value, $message),
                str_contains($typeStr, 'bool') => $this->assertIsBool($value, $message),
                str_contains($typeStr, 'int') => $this->assertIsInt($value, $message),
                str_contains($typeStr, 'float') => $this->assertIsFloat($value, $message),
                str_contains($typeStr, 'string') => $this->assertIsString($value, $message),
                default => $this->assertInstanceOf($typeStr, $value, $message),
            };
        }
    }

    private function waitForEvent(): void
    {
        for ($attempt = 0; $attempt < 10; ++$attempt) {
            $events = $this->offlineEventService->list(['=EVENT_NAME' => self::EVENT_CODE], ['ID' => 'DESC'])->getEvents();
            if ($events !== []) {
                return;
            }

            usleep(500_000);
        }
    }
}
