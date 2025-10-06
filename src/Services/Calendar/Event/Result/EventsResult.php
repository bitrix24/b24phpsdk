<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Sally Fancen <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Calendar\Event\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;

/**
 * Events list result
 */
class EventsResult extends AbstractResult
{
    /**
     * Get events
     * @return EventItemResult[]
     */
    public function getEvents(): array
    {
        $events = [];
        foreach ($this->getCoreResponse()->getResponseData()->getResult() as $event) {
            $events[] = new EventItemResult($event);
        }

        return $events;
    }
}
