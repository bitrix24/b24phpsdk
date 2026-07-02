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

namespace Bitrix24\SDK\Services\IMBot\Event\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Result for imbot.v2.Event.get.
 *
 * Response shape:
 * {
 *   result: {
 *     events: [{ eventId, type, date, data }],
 *     nextOffset: int,
 *     hasMore: bool
 *   }
 * }
 *
 * @link https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/events/event-get.html
 */
class EventsResult extends AbstractResult
{
    /**
     * Returns all events from the current page.
     *
     * @return list<EventItemResult>
     *
     * @throws BaseException
     */
    public function getEvents(): array
    {
        $events = $this->getCoreResponse()->getResponseData()->getResult()['events'] ?? [];

        return array_map(
            static fn(array $event): EventItemResult => new EventItemResult($event),
            $events
        );
    }

    /**
     * Returns the offset to pass to the next imbot.v2.Event.get call to acknowledge processed events.
     * Returns null when there are no events.
     *
     * @throws BaseException
     */
    public function getNextOffset(): ?int
    {
        $value = $this->getCoreResponse()->getResponseData()->getResult()['nextOffset'] ?? null;

        return $value !== null ? (int)$value : null;
    }

    /**
     * Returns true when there are more unprocessed events in the queue.
     *
     * @throws BaseException
     */
    public function isHasMore(): bool
    {
        return (bool)($this->getCoreResponse()->getResponseData()->getResult()['hasMore'] ?? false);
    }
}
