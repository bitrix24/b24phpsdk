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
use Bitrix24\SDK\Services\Main\Service\OfflineEvent;
use Bitrix24\SDK\Services\Task\Events\OnTaskAdd\OnTaskAdd;
use Bitrix24\SDK\Services\Task\Service\Task;
use Bitrix24\SDK\Services\Task\Service\TaskItemBuilder;
use Bitrix24\SDK\Tests\Integration\Factory;
use Bitrix24\SDK\Tests\Integration\Services\Main\OfflineEventTrigger;
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

    private Task $webhookTaskService;

    private int $userId = 0;

    #[\Override]
    protected function setUp(): void
    {
        // build the webhook trigger FIRST, before application credentials blank BITRIX24_WEBHOOK
        $webhookServiceBuilder = OfflineEventTrigger::webhookServiceBuilder();
        $this->webhookTaskService = $webhookServiceBuilder->getTaskScope()->task();
        $this->userId = $webhookServiceBuilder->getMainScope()->main()->getCurrentUserProfile()->getUserProfile()->ID;

        $mainServiceBuilder = Factory::getServiceBuilder(true)->getMainScope();
        $this->offlineEventService = $mainServiceBuilder->offlineEvent();
        $this->eventService = $mainServiceBuilder->event();
    }

    #[\Override]
    protected function tearDown(): void
    {
        // drain the offline queue so repeated runs start clean
        try {
            $this->offlineEventService->get(['=EVENT_NAME' => self::EVENT_CODE], null, true);
        } catch (\Throwable) {
        }

        try {
            $this->eventService->unbind(self::EVENT_CODE, self::HANDLER_URL, eventType: EventType::offline);
        } catch (\Throwable) {
        }
    }

    #[Test]
    #[TestDox('offline-events lifecycle: bind, trigger, list, get, error, clear')]
    public function testOfflineEventLifecycle(): void
    {
        // 1. register an offline handler so changes are queued (re-bind cleanly)
        try {
            $this->eventService->unbind(self::EVENT_CODE, self::HANDLER_URL, eventType: EventType::offline);
        } catch (\Throwable) {
        }

        $this->eventService->bind(self::EVENT_CODE, self::HANDLER_URL, eventType: EventType::offline);

        // 2. trigger the event from a different context (incoming webhook); an application does not
        //    receive offline events for its own REST changes
        $taskId = $this->webhookTaskService->add(
            new TaskItemBuilder('b24phpsdk offline-event test', $this->userId, $this->userId)
        )->task()->id;

        try {
            // 3. read the queue without changing state
            $events = $this->pollForEvents();
            $this->assertNotEmpty($events, 'offline queue did not receive the triggered event');
            $this->assertSame(self::EVENT_CODE, $events[0]->EVENT_NAME);

            // 4. reserve a packet (clear=false) — returns a process id usable by error()/clear()
            $packet = $this->offlineEventService->get(['=EVENT_NAME' => self::EVENT_CODE], null, false);
            $processId = $packet->getProcessId();
            $this->assertNotEmpty($processId);
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
            $this->webhookTaskService->delete($taskId);
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
