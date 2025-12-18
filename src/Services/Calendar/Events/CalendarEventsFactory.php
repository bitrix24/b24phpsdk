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

namespace Bitrix24\SDK\Services\Calendar\Events;

use Bitrix24\SDK\Core\Contracts\Events\EventInterface;
use Bitrix24\SDK\Core\Contracts\Events\EventsFabricInterface;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Services\Calendar\Events\OnCalendarSectionAdd\OnCalendarSectionAdd;
use Bitrix24\SDK\Services\Calendar\Events\OnCalendarSectionDelete\OnCalendarSectionDelete;
use Bitrix24\SDK\Services\Calendar\Events\OnCalendarSectionUpdate\OnCalendarSectionUpdate;
use Symfony\Component\HttpFoundation\Request;

readonly class CalendarEventsFactory implements EventsFabricInterface
{
    #[\Override]
    public function isSupport(string $eventCode): bool
    {
        return in_array(strtoupper($eventCode), [
            OnCalendarSectionAdd::CODE,
            OnCalendarSectionUpdate::CODE,
            OnCalendarSectionDelete::CODE,
        ], true);
    }

    /**
     * @throws InvalidArgumentException
     */
    #[\Override]
    public function create(Request $eventRequest): EventInterface
    {
        $eventPayload = $eventRequest->request->all();
        if (!array_key_exists('event', $eventPayload)) {
            throw new InvalidArgumentException('«event» key not found in event payload');
        }

        return match ($eventPayload['event']) {
            OnCalendarSectionAdd::CODE => new OnCalendarSectionAdd($eventRequest),
            OnCalendarSectionUpdate::CODE => new OnCalendarSectionUpdate($eventRequest),
            OnCalendarSectionDelete::CODE => new OnCalendarSectionDelete($eventRequest),
            default => throw new InvalidArgumentException(
                sprintf('Unexpected event code «%s»', $eventPayload['event'])
            ),
        };
    }
}
