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

namespace Bitrix24\SDK\Services\IMOpenLines\Events;

use Bitrix24\SDK\Core\Contracts\Events\EventInterface;
use Bitrix24\SDK\Core\Contracts\Events\EventsFabricInterface;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Services\IMOpenLines\Events\OnSessionStart\OnSessionStart;
use Bitrix24\SDK\Services\IMOpenLines\Events\OnOpenLineMessageAdd\OnOpenLineMessageAdd;
use Bitrix24\SDK\Services\IMOpenLines\Events\OnOpenLineMessageUpdate\OnOpenLineMessageUpdate;
use Bitrix24\SDK\Services\IMOpenLines\Events\OnOpenLineMessageDelete\OnOpenLineMessageDelete;
use Bitrix24\SDK\Services\IMOpenLines\Events\OnSessionFinish\OnSessionFinish;
use Symfony\Component\HttpFoundation\Request;

readonly class IMOpenLinesEventsFactory implements EventsFabricInterface
{
    public function isSupport(string $eventCode): bool
    {
        return in_array(strtoupper($eventCode), [
            OnSessionStart::CODE,
            OnOpenLineMessageAdd::CODE,
            OnOpenLineMessageUpdate::CODE,
            OnOpenLineMessageDelete::CODE,
            OnSessionFinish::CODE,
        ], true);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function create(Request $eventRequest): EventInterface
    {
        $eventPayload = $eventRequest->request->all();
        if (!array_key_exists('event', $eventPayload)) {
            throw new InvalidArgumentException('«event» key not found in event payload');
        }

        return match ($eventPayload['event']) {
            OnSessionStart::CODE => new OnSessionStart($eventRequest),
            OnOpenLineMessageAdd::CODE => new OnOpenLineMessageAdd($eventRequest),
            OnOpenLineMessageUpdate::CODE => new OnOpenLineMessageUpdate($eventRequest),
            OnOpenLineMessageDelete::CODE => new OnOpenLineMessageDelete($eventRequest),
            OnSessionFinish::CODE => new OnSessionFinish($eventRequest),
            default => throw new InvalidArgumentException(
                sprintf('Unexpected event code «%s»', $eventPayload['event'])
            ),
        };
    }
}