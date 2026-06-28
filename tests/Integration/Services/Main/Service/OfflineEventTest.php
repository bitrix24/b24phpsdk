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

namespace Bitrix24\SDK\Tests\Integration\Services\Main\Service;

use Bitrix24\SDK\Services\Main\Service\Event;
use Bitrix24\SDK\Services\Main\Service\EventType;
use Bitrix24\SDK\Services\Main\Service\Main;
use Bitrix24\SDK\Services\Main\Service\OfflineEvent;
use Bitrix24\SDK\Services\Task\Events\OnTaskAdd\OnTaskAdd;
use Bitrix24\SDK\Services\Task\Service\Task;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(OfflineEvent::class)]
#[CoversMethod(OfflineEvent::class, 'get')]
#[CoversMethod(OfflineEvent::class, 'list')]
#[CoversMethod(OfflineEvent::class, 'clear')]
#[CoversMethod(OfflineEvent::class, 'error')]
class OfflineEventTest extends TestCase
{
    private const string EVENT_CODE = OnTaskAdd::CODE;

    private const string HANDLER_URL = 'https://example.com/b24phpsdk-offline-handler';

    private OfflineEvent $offlineEventService;

    private Event $eventService;

    private Task $taskService;

    private Main $mainService;

    private bool $isHandlerBound = false;

    #[\Override]
    protected function setUp(): void
    {
        $serviceBuilder = Factory::getServiceBuilder(true);
        $this->offlineEventService = $serviceBuilder->getMainScope()->offlineEvent();
        $this->eventService = $serviceBuilder->getMainScope()->event();
        $this->mainService = $serviceBuilder->getMainScope()->main();
        $this->taskService = $serviceBuilder->getTaskScope()->task();
    }

    #[\Override]
    protected function tearDown(): void
    {
        // drain the offline queue so repeated runs start clean
        try {
            $this->offlineEventService->get(['=EVENT_NAME' => self::EVENT_CODE], null, true);
        } catch (\Throwable) {
        }

        if ($this->isHandlerBound) {
            try {
                $this->eventService->unbind(self::EVENT_CODE, self::HANDLER_URL, eventType: EventType::offline);
            } catch (\Throwable) {
            }
        }
    }

    #[Test]
    #[TestDox('offline-events lifecycle: bind, trigger, list, get, error, clear')]
    public function testOfflineEventLifecycle(): void
    {
        // 1. register an offline handler so changes are queued
        $this->eventService->bind(self::EVENT_CODE, self::HANDLER_URL, eventType: EventType::offline);
        $this->isHandlerBound = true;

        // 2. trigger the event with an entity change
        $responsibleId = $this->mainService->getCurrentUserProfile()->getUserProfile()->ID;
        $taskId = $this->taskService->add([
            'TITLE' => 'b24phpsdk offline-event test',
            'RESPONSIBLE_ID' => $responsibleId,
        ])->task()->id;

        try {
            // 3. read the queue without changing state
            $events = $this->pollForEvents();
            $this->assertNotEmpty($events, 'offline queue did not receive the triggered event');
            $this->assertSame(self::EVENT_CODE, $events[0]->EVENT_NAME);

            // 4. reserve a packet (clear=false) — returns a process id usable by error()/clear()
            $packet = $this->offlineEventService->get(['=EVENT_NAME' => self::EVENT_CODE], null, false);
            $processId = $packet->getProcessId();
            $this->assertNotNull($processId);
            $this->assertNotEmpty($packet->getEvents());

            $messageId = $packet->getEvents()[0]->MESSAGE_ID;

            // 5. mark a record as errored
            $this->assertTrue(
                $this->offlineEventService->error($processId, [$messageId])->isSuccess()
            );

            // 6. clear the reserved packet
            $this->assertTrue(
                $this->offlineEventService->clear($processId)->isSuccess()
            );
        } finally {
            $this->taskService->delete($taskId);
        }
    }

    /**
     * Offline events may take a moment to appear in the queue after the change.
     *
     * @return \Bitrix24\SDK\Services\Main\Result\OfflineEventItemResult[]
     */
    private function pollForEvents(): array
    {
        for ($attempt = 0; $attempt < 10; ++$attempt) {
            $events = $this->offlineEventService->list(['=EVENT_NAME' => self::EVENT_CODE], ['ID' => 'DESC'])->getEvents();
            if ($events !== []) {
                return $events;
            }

            usleep(500_000);
        }

        return [];
    }
}
