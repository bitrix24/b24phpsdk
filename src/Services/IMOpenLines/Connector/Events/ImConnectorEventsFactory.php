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

namespace Bitrix24\SDK\Services\IMOpenLines\Connector\Events;

use Bitrix24\SDK\Core\Contracts\Events\EventInterface;
use Bitrix24\SDK\Core\Contracts\Events\EventsFabricInterface;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;

use Bitrix24\SDK\Services\IMOpenLines\Connector\Events\OnImConnectorMessageAdd\OnImConnectorMessageAdd;
use Bitrix24\SDK\Services\IMOpenLines\Connector\Events\OnImConnectorDialogStart\OnImConnectorDialogStart;
use Bitrix24\SDK\Services\IMOpenLines\Connector\Events\OnImConnectorMessageUpdate\OnImConnectorMessageUpdate;
use Bitrix24\SDK\Services\IMOpenLines\Connector\Events\OnImConnectorMessageDelete\OnImConnectorMessageDelete;
use Bitrix24\SDK\Services\IMOpenLines\Connector\Events\OnImConnectorDialogFinish\OnImConnectorDialogFinish;
use Bitrix24\SDK\Services\IMOpenLines\Connector\Events\OnImConnectorStatusDelete\OnImConnectorStatusDelete;
use Bitrix24\SDK\Services\IMOpenLines\Connector\Events\OnImConnectorLineDelete\OnImConnectorLineDelete;

use Symfony\Component\HttpFoundation\Request;

readonly class ImConnectorEventsFactory implements EventsFabricInterface
{
    #[\Override]
    public function isSupport(string $eventCode): bool
    {
        return in_array(strtoupper($eventCode), [
            OnImConnectorMessageAdd::CODE,
            OnImConnectorDialogStart::CODE,
            OnImConnectorMessageUpdate::CODE,
            OnImConnectorMessageDelete::CODE,
            OnImConnectorDialogFinish::CODE,
            OnImConnectorStatusDelete::CODE,
            OnImConnectorLineDelete::CODE,
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
            OnImConnectorMessageAdd::CODE => new OnImConnectorMessageAdd($eventRequest),
            OnImConnectorDialogStart::CODE => new OnImConnectorDialogStart($eventRequest),
            OnImConnectorMessageUpdate::CODE => new OnImConnectorMessageUpdate($eventRequest),
            OnImConnectorMessageDelete::CODE => new OnImConnectorMessageDelete($eventRequest),
            OnImConnectorDialogFinish::CODE => new OnImConnectorDialogFinish($eventRequest),
            OnImConnectorStatusDelete::CODE => new OnImConnectorStatusDelete($eventRequest),
            OnImConnectorLineDelete::CODE => new OnImConnectorLineDelete($eventRequest),
            default => throw new InvalidArgumentException(
                sprintf('Unexpected event code «%s»', $eventPayload['event'])
            ),
        };
    }
}
